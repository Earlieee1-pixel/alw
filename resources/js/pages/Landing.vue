<script setup>
import { ref } from 'vue'
import {
    Bars3Icon,
    XMarkIcon,
    GlobeAltIcon,
    VideoCameraIcon,
    UsersIcon,
    AcademicCapIcon,
    ChartBarIcon,
    ShieldCheckIcon,
    ChevronDownIcon,
    ArrowRightIcon,
    PlayCircleIcon,
    SignalIcon,
    UserGroupIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon } from '@heroicons/vue/24/solid'

// Mobile nav toggle
const mobileMenuOpen = ref(false)

// Nav links
const navLinks = [
    { label: 'About',    href: '#about' },
    { label: 'Features', href: '#features' },
    { label: 'Network',  href: '#network' },
    { label: 'Training', href: '#training' },
]

// Core features sa platform
const features = [
    {
        icon: VideoCameraIcon,
        title: 'Live Video Conferencing',
        description:
            'Host real-time video calls with your entire network regardless of location. Powered by Jitsi — no installs, no subscriptions.',
    },
    {
        icon: AcademicCapIcon,
        title: 'Training Video Library',
        description:
            'Centralized video content for onboarding, product knowledge, and leadership development. Organized by category and accessible anytime.',
    },
    {
        icon: ChartBarIcon,
        title: 'Network Analytics',
        description:
            'Track growth across your downlines in real time. See who is active, who is growing, and where your network needs support.',
    },
    {
        icon: UserGroupIcon,
        title: 'Downline Management',
        description:
            'Visualize your entire organization in a clear tree structure. Manage members, monitor performance, and stay connected at every level.',
    },
    {
        icon: SignalIcon,
        title: 'Global Reach',
        description:
            'Built for international networks. Your team in the Philippines, the US, the Middle East — all connected on one unified platform.',
    },
    {
        icon: LockClosedIcon,
        title: 'Secure & Private',
        description:
            'Role-based access ensures each member sees only what they should. Admins, leaders, and members — each with defined boundaries.',
    },
]

// Stats section
const stats = [
    { value: '10,000+', label: 'Active Members' },
    { value: '30+',     label: 'Countries Reached' },
    { value: '500+',    label: 'Training Videos' },
    { value: '99.9%',   label: 'Platform Uptime' },
]

// Membership tiers / steps
const steps = [
    { number: '01', title: 'Get Invited',     description: 'Every ALW member joins through a personal referral link from an existing member.' },
    { number: '02', title: 'Create Account',  description: 'Register with your invite code, set up your profile, and get instant access to the platform.' },
    { number: '03', title: 'Join Your Team',  description: 'Connect with your upline, attend your first live call, and start your training journey.' },
    { number: '04', title: 'Build & Grow',    description: 'Invite others, grow your downline, and climb the leadership ranks within the organization.' },
]
</script>

<template>
    <div class="bg-cream-200 min-h-screen font-sans text-teal-900">

        <!-- ===================== NAVIGATION ===================== -->
        <header class="fixed top-0 inset-x-0 z-50 bg-cream-200/90 backdrop-blur-md border-b border-teal-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    <!-- Logo -->
                    <a href="#" class="flex items-center gap-2.5 group">
                        <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center shadow-sm">
                            <GlobeAltIcon class="w-5 h-5 text-cream-200" />
                        </div>
                        <span class="text-lg font-bold tracking-tight text-teal-700 group-hover:text-teal-500 transition-colors">
                            ALW
                        </span>
                        <span class="hidden sm:block text-xs text-teal-500 font-medium tracking-widest uppercase mt-0.5">
                            Atomic Legendary Warriors
                        </span>
                    </a>

                    <!-- Desktop nav -->
                    <nav class="hidden md:flex items-center gap-8">
                        <a
                            v-for="link in navLinks"
                            :key="link.label"
                            :href="link.href"
                            class="text-sm font-medium text-teal-700 hover:text-teal-500 transition-colors"
                        >
                            {{ link.label }}
                        </a>
                    </nav>

                    <!-- CTA + mobile toggle -->
                    <div class="flex items-center gap-3">
                        <a
                            href="#join"
                            class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors shadow-sm"
                        >
                            Join the Network
                            <ArrowRightIcon class="w-4 h-4" />
                        </a>
                        <button
                            class="md:hidden p-2 rounded-lg text-teal-700 hover:bg-teal-100 transition-colors"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            aria-label="Toggle navigation"
                        >
                            <XMarkIcon v-if="mobileMenuOpen" class="w-5 h-5" />
                            <Bars3Icon v-else class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t border-teal-100 bg-cream-200 px-6 pb-4">
                <nav class="flex flex-col gap-1 pt-3">
                    <a
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.href"
                        class="px-3 py-2 rounded-lg text-sm font-medium text-teal-700 hover:bg-teal-50 hover:text-teal-500 transition-colors"
                        @click="mobileMenuOpen = false"
                    >
                        {{ link.label }}
                    </a>
                    <a
                        href="#join"
                        class="mt-2 px-3 py-2 rounded-lg bg-teal-500 text-cream-200 text-sm font-semibold text-center hover:bg-teal-600 transition-colors"
                        @click="mobileMenuOpen = false"
                    >
                        Join the Network
                    </a>
                </nav>
            </div>
        </header>

        <!-- ===================== HERO ===================== -->
        <section class="relative pt-32 pb-24 lg:pt-44 lg:pb-32 overflow-hidden">

            <!-- Subtle background texture -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[600px] rounded-full bg-teal-500/8 blur-3xl" />
                <div class="absolute bottom-0 right-0 w-[400px] h-[400px] rounded-full bg-cream-400/30 blur-3xl" />
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">

                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/20 mb-8">
                        <GlobeAltIcon class="w-3.5 h-3.5 text-teal-500" />
                        <span class="text-xs font-semibold text-teal-600 tracking-wide uppercase">A Global Movement</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-teal-800 leading-tight tracking-tight">
                        One Platform.<br />
                        <span class="text-teal-500">Infinite Reach.</span>
                    </h1>

                    <p class="mt-6 text-lg text-teal-700/80 leading-relaxed max-w-2xl mx-auto">
                        Atomic Legendary Warriors connects leaders, builders, and visionaries across the globe.
                        Train, collaborate, and grow your network — all in one place.
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a
                            href="#join"
                            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-teal-500 text-cream-200 font-semibold text-sm hover:bg-teal-600 transition-colors shadow-md shadow-teal-500/20"
                        >
                            Get Started
                            <ArrowRightIcon class="w-4 h-4" />
                        </a>
                        <a
                            href="#features"
                            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl border border-teal-300 text-teal-700 font-semibold text-sm hover:bg-teal-50 transition-colors"
                        >
                            <PlayCircleIcon class="w-4 h-4 text-teal-500" />
                            See How It Works
                        </a>
                    </div>
                </div>

                <!-- Stats bar -->
                <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-px bg-teal-200 rounded-2xl overflow-hidden shadow-sm border border-teal-200">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="bg-cream-200 px-6 py-6 text-center"
                    >
                        <div class="text-3xl font-bold text-teal-500">{{ stat.value }}</div>
                        <div class="mt-1 text-xs font-medium text-teal-600/70 uppercase tracking-wide">{{ stat.label }}</div>
                    </div>
                </div>
            </div>

            <!-- Scroll cue -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 text-teal-400">
                <span class="text-xs font-medium tracking-widest uppercase">Scroll</span>
                <ChevronDownIcon class="w-4 h-4 animate-bounce" />
            </div>
        </section>

        <!-- ===================== ABOUT ===================== -->
        <section id="about" class="py-24 bg-teal-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-16 items-center">

                    <div>
                        <span class="text-xs font-semibold tracking-widest uppercase text-teal-400">Who We Are</span>
                        <h2 class="mt-3 text-3xl lg:text-4xl font-bold text-cream-200 leading-tight">
                            Built by networkers,<br />for networkers.
                        </h2>
                        <p class="mt-5 text-teal-300 leading-relaxed">
                            ALW is a community of driven individuals united by a shared vision — to build something
                            meaningful together, across borders and time zones. What started as a small circle has grown
                            into a global organization spanning dozens of countries.
                        </p>
                        <p class="mt-4 text-teal-300 leading-relaxed">
                            This platform exists to support that mission. It's the central hub where every ALW member
                            can learn, connect, and lead — whether they're just starting out or running a team of hundreds.
                        </p>

                        <ul class="mt-8 space-y-3">
                            <li
                                v-for="item in ['Community-first approach', 'Transparent network tracking', 'Continuous learning resources', 'Leadership development at every level']"
                                :key="item"
                                class="flex items-center gap-3 text-sm text-teal-200"
                            >
                                <CheckCircleIcon class="w-5 h-5 text-teal-400 shrink-0" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>

                    <!-- Visual card -->
                    <div class="relative">
                        <div class="rounded-2xl bg-teal-700/50 border border-teal-600/40 p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-teal-500 flex items-center justify-center">
                                    <GlobeAltIcon class="w-5 h-5 text-cream-200" />
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-cream-200">Global Network Status</div>
                                    <div class="text-xs text-teal-400">Live across all regions</div>
                                </div>
                                <div class="ml-auto flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                                    <span class="text-xs text-green-400 font-medium">Online</span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="region in [
                                        { name: 'Southeast Asia',   members: '4,200', pct: '85' },
                                        { name: 'Middle East',      members: '2,800', pct: '62' },
                                        { name: 'North America',    members: '1,900', pct: '45' },
                                        { name: 'Europe',           members: '1,100', pct: '28' },
                                    ]"
                                    :key="region.name"
                                    class="space-y-1.5"
                                >
                                    <div class="flex justify-between text-xs">
                                        <span class="text-teal-300 font-medium">{{ region.name }}</span>
                                        <span class="text-teal-400">{{ region.members }} members</span>
                                    </div>
                                    <div class="h-1.5 bg-teal-600/50 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-teal-400 rounded-full transition-all"
                                            :style="{ width: region.pct + '%' }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating badge -->
                        <div class="absolute -bottom-4 -right-4 bg-cream-200 rounded-xl px-4 py-3 shadow-lg border border-teal-100">
                            <div class="text-xl font-bold text-teal-500">30+</div>
                            <div class="text-xs text-teal-600 font-medium">Countries</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== FEATURES ===================== -->
        <section id="features" class="py-24 bg-cream-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">

                <div class="text-center max-w-2xl mx-auto mb-16">
                    <span class="text-xs font-semibold tracking-widest uppercase text-teal-500">Platform Features</span>
                    <h2 class="mt-3 text-3xl lg:text-4xl font-bold text-teal-800 leading-tight">
                        Everything your network needs,<br />nothing it doesn't.
                    </h2>
                    <p class="mt-4 text-teal-600/80">
                        Purpose-built tools designed around the real challenges of managing and growing a global network.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group bg-cream-200 rounded-2xl p-6 border border-teal-100 hover:border-teal-300 hover:shadow-md hover:shadow-teal-500/5 transition-all duration-200"
                    >
                        <div class="w-11 h-11 rounded-xl bg-teal-500/10 flex items-center justify-center mb-5 group-hover:bg-teal-500 transition-colors duration-200">
                            <component
                                :is="feature.icon"
                                class="w-5 h-5 text-teal-500 group-hover:text-cream-200 transition-colors duration-200"
                            />
                        </div>
                        <h3 class="text-base font-semibold text-teal-800 mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-teal-600/80 leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== VIDEO CALL HIGHLIGHT ===================== -->
        <section id="network" class="py-24 bg-teal-500">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-16 items-center">

                    <!-- Visual mockup -->
                    <div class="order-2 lg:order-1">
                        <div class="rounded-2xl bg-teal-600/60 border border-teal-400/30 overflow-hidden shadow-xl">

                            <!-- Fake video call UI -->
                            <div class="bg-teal-800/80 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-red-400" />
                                    <div class="w-2 h-2 rounded-full bg-yellow-400" />
                                    <div class="w-2 h-2 rounded-full bg-green-400" />
                                </div>
                                <span class="text-xs text-teal-300 font-medium">ALW Weekly Briefing</span>
                                <div class="flex items-center gap-1 text-xs text-teal-400">
                                    <UsersIcon class="w-3.5 h-3.5" />
                                    <span>48</span>
                                </div>
                            </div>

                            <div class="p-4 grid grid-cols-3 gap-2">
                                <div
                                    v-for="(participant, i) in [
                                        { name: 'Maria S.',  region: 'PH', active: true },
                                        { name: 'Ahmed K.',  region: 'AE', active: false },
                                        { name: 'James R.',  region: 'US', active: false },
                                        { name: 'Lena M.',   region: 'DE', active: false },
                                        { name: 'Carlos V.', region: 'MX', active: false },
                                        { name: 'Yuki T.',   region: 'JP', active: false },
                                    ]"
                                    :key="i"
                                    class="aspect-video rounded-lg flex flex-col items-center justify-center gap-1"
                                    :class="participant.active ? 'bg-teal-500/40 ring-2 ring-cream-200/40' : 'bg-teal-700/60'"
                                >
                                    <div class="w-7 h-7 rounded-full bg-teal-400/40 flex items-center justify-center">
                                        <UsersIcon class="w-3.5 h-3.5 text-cream-200" />
                                    </div>
                                    <span class="text-xs text-cream-200/80 font-medium">{{ participant.name }}</span>
                                    <span class="text-[10px] text-teal-300">{{ participant.region }}</span>
                                </div>
                            </div>

                            <div class="px-4 pb-4 flex items-center justify-center gap-3">
                                <div class="px-4 py-2 rounded-lg bg-teal-700/60 text-xs text-cream-200 font-medium">Mute</div>
                                <div class="px-4 py-2 rounded-lg bg-red-500/80 text-xs text-cream-200 font-medium">End</div>
                                <div class="px-4 py-2 rounded-lg bg-teal-700/60 text-xs text-cream-200 font-medium">Share</div>
                            </div>
                        </div>
                    </div>

                    <div class="order-1 lg:order-2">
                        <span class="text-xs font-semibold tracking-widest uppercase text-cream-200/70">Live Collaboration</span>
                        <h2 class="mt-3 text-3xl lg:text-4xl font-bold text-cream-200 leading-tight">
                            Your team is always<br />within reach.
                        </h2>
                        <p class="mt-5 text-cream-200/75 leading-relaxed">
                            Host weekly briefings, one-on-one coaching sessions, or large-scale presentations — all
                            without leaving the platform. Jitsi-powered calls mean no accounts, no downloads, just a link.
                        </p>

                        <ul class="mt-8 space-y-4">
                            <li
                                v-for="point in [
                                    'No app install required — works in any browser',
                                    'Create private rooms for leadership meetings',
                                    'Share your screen for live training and demos',
                                    'Record sessions and save to the training library',
                                ]"
                                :key="point"
                                class="flex items-start gap-3 text-sm text-cream-200/80"
                            >
                                <CheckCircleIcon class="w-5 h-5 text-cream-300 shrink-0 mt-0.5" />
                                {{ point }}
                            </li>
                        </ul>

                        <a
                            href="#join"
                            class="mt-10 inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-cream-200 text-teal-700 font-semibold text-sm hover:bg-cream-100 transition-colors shadow-md"
                        >
                            <VideoCameraIcon class="w-4 h-4" />
                            Start Your First Call
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== TRAINING ===================== -->
        <section id="training" class="py-24 bg-cream-200">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-16 items-center">

                    <div>
                        <span class="text-xs font-semibold tracking-widest uppercase text-teal-500">Knowledge Hub</span>
                        <h2 class="mt-3 text-3xl lg:text-4xl font-bold text-teal-800 leading-tight">
                            Train smarter.<br />Lead better.
                        </h2>
                        <p class="mt-5 text-teal-600/80 leading-relaxed">
                            The ALW training library is your team's single source of truth. From product walkthroughs to
                            leadership frameworks — every video is organized, searchable, and always available.
                        </p>

                        <div class="mt-8 grid grid-cols-2 gap-4">
                            <div
                                v-for="cat in [
                                    { icon: AcademicCapIcon,  label: 'Onboarding',        count: '24 videos' },
                                    { icon: ChartBarIcon,     label: 'Leadership',         count: '18 videos' },
                                    { icon: ShieldCheckIcon,  label: 'Compliance',         count: '12 videos' },
                                    { icon: UsersIcon,        label: 'Network Building',   count: '31 videos' },
                                ]"
                                :key="cat.label"
                                class="flex items-center gap-3 p-4 rounded-xl border border-teal-100 bg-teal-50/50 hover:border-teal-300 transition-colors"
                            >
                                <div class="w-9 h-9 rounded-lg bg-teal-500/10 flex items-center justify-center shrink-0">
                                    <component :is="cat.icon" class="w-4 h-4 text-teal-500" />
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-teal-800">{{ cat.label }}</div>
                                    <div class="text-xs text-teal-500">{{ cat.count }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video card mockup -->
                    <div class="space-y-3">
                        <div
                            v-for="(video, i) in [
                                { title: 'Getting Started with ALW',         duration: '12:40', badge: 'Onboarding', new: true },
                                { title: 'Building Your First Downline',     duration: '18:22', badge: 'Network',    new: false },
                                { title: 'Leadership Principles for Growth', duration: '24:05', badge: 'Leadership', new: false },
                                { title: 'Understanding the ALW Framework',  duration: '09:57', badge: 'Compliance', new: false },
                            ]"
                            :key="i"
                            class="flex items-center gap-4 p-4 rounded-xl bg-teal-50 border border-teal-100 hover:border-teal-300 hover:bg-teal-50/80 transition-all cursor-pointer group"
                        >
                            <div class="w-12 h-12 rounded-lg bg-teal-500/15 flex items-center justify-center shrink-0 group-hover:bg-teal-500 transition-colors">
                                <PlayCircleIcon class="w-5 h-5 text-teal-500 group-hover:text-cream-200 transition-colors" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-teal-800 truncate">{{ video.title }}</span>
                                    <span v-if="video.new" class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-teal-500 text-cream-200 uppercase tracking-wide">New</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-teal-500 font-medium">{{ video.badge }}</span>
                                    <span class="text-teal-300">·</span>
                                    <span class="text-xs text-teal-400">{{ video.duration }}</span>
                                </div>
                            </div>
                            <ArrowRightIcon class="w-4 h-4 text-teal-300 group-hover:text-teal-500 transition-colors shrink-0" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== HOW IT WORKS ===================== -->
        <section class="py-24 bg-teal-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">

                <div class="text-center max-w-2xl mx-auto mb-16">
                    <span class="text-xs font-semibold tracking-widest uppercase text-teal-400">Getting Started</span>
                    <h2 class="mt-3 text-3xl lg:text-4xl font-bold text-cream-200 leading-tight">
                        From invite to impact<br />in four steps.
                    </h2>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="step in steps"
                        :key="step.number"
                        class="relative p-6 rounded-2xl bg-teal-700/40 border border-teal-600/30 hover:border-teal-500/50 transition-colors"
                    >
                        <div class="text-4xl font-black text-teal-600/40 mb-4 select-none">{{ step.number }}</div>
                        <h3 class="text-base font-semibold text-cream-200 mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-teal-300/80 leading-relaxed">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== CTA ===================== -->
        <section id="join" class="py-24 bg-cream-200">
            <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/20 mb-8">
                    <ShieldCheckIcon class="w-3.5 h-3.5 text-teal-500" />
                    <span class="text-xs font-semibold text-teal-600 tracking-wide uppercase">Invite Only</span>
                </div>

                <h2 class="text-3xl lg:text-5xl font-bold text-teal-800 leading-tight">
                    Ready to become<br />
                    an Atomic Legend?
                </h2>
                <p class="mt-5 text-teal-600/80 text-lg leading-relaxed max-w-xl mx-auto">
                    ALW membership is by invitation. If you've received a referral link from an existing member,
                    you're one step away from joining the network.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        href="/register"
                        class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-teal-500 text-cream-200 font-semibold hover:bg-teal-600 transition-colors shadow-lg shadow-teal-500/20 text-sm"
                    >
                        Register with Invite Code
                        <ArrowRightIcon class="w-4 h-4" />
                    </a>
                    <a
                        href="/login"
                        class="inline-flex items-center gap-2 px-8 py-4 rounded-xl border border-teal-300 text-teal-700 font-semibold hover:bg-teal-50 transition-colors text-sm"
                    >
                        <LockClosedIcon class="w-4 h-4" />
                        Member Login
                    </a>
                </div>
            </div>
        </section>

        <!-- ===================== FOOTER ===================== -->
        <footer class="bg-teal-900 py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">

                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-teal-500 flex items-center justify-center">
                            <GlobeAltIcon class="w-4 h-4 text-cream-200" />
                        </div>
                        <span class="text-sm font-bold text-cream-200 tracking-tight">ALW</span>
                        <span class="text-xs text-teal-500 font-medium">Atomic Legendary Warriors</span>
                    </div>

                    <p class="text-xs text-teal-500 text-center">
                        &copy; {{ new Date().getFullYear() }} Atomic Legendary Warriors. All rights reserved.
                    </p>

                    <div class="flex items-center gap-6">
                        <a href="#" class="text-xs text-teal-500 hover:text-teal-300 transition-colors">Privacy</a>
                        <a href="#" class="text-xs text-teal-500 hover:text-teal-300 transition-colors">Terms</a>
                        <a href="#" class="text-xs text-teal-500 hover:text-teal-300 transition-colors">Contact</a>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>
