<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

// ログイン中のユーザー
const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);

// ログアウト：localStorageのuser_idをクリアしてからログアウト（再ログイン防止）
const handleLogout = () => {
    localStorage.removeItem('user_id');
    router.post(route('logout'));
};

// ナビゲーション項目
const navItems = [
    { label: 'ダッシュボード', route: 'admin.dashboard', pattern: 'admin.dashboard' },
    { label: 'アポイント', route: 'admin.appointments.index', pattern: 'admin.appointments.*' },
    { label: '施設管理', route: 'admin.facilities.index', pattern: 'admin.facilities.*' },
    { label: '通知設定', route: 'admin.notification-settings.index', pattern: 'admin.notification-settings.*' },
    { label: '部署電話番号', route: 'admin.departments.index', pattern: 'admin.departments.*' },
    { label: 'お知らせ', route: 'admin.announcements.index', pattern: 'admin.announcements.*' },
];

// 現在時刻
const currentTime = ref('');
let timeInterval;
function formatDateTime(date) {
    return new Intl.DateTimeFormat('ja-JP', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', weekday: 'short',
    }).format(date);
}
onMounted(() => {
    currentTime.value = formatDateTime(new Date());
    timeInterval = setInterval(() => {
        currentTime.value = formatDateTime(new Date());
    }, 1000);
});
onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
});
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <nav class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('admin.dashboard')" class="flex items-center gap-2 text-white">
                                <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-lg">🏢</span>
                                <span class="text-lg font-bold tracking-wide">受付管理システム</span>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-1 sm:ml-8 sm:flex sm:items-center">
                            <Link
                                v-for="item in navItems"
                                :key="item.route"
                                :href="route(item.route)"
                                :class="[
                                    'inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150',
                                    route().current(item.pattern)
                                        ? 'bg-white/20 text-white'
                                        : 'text-blue-100 hover:bg-white/10 hover:text-white'
                                ]"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <!-- 右側：ログインユーザー + 現在時刻 + ログアウト -->
                    <div class="hidden sm:flex sm:items-center sm:gap-4">
                        <span v-if="authUser" class="flex items-center gap-1.5 text-sm text-white font-medium">
                            <span class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs">👤</span>
                            {{ authUser.name }}
                        </span>
                        <span class="hidden lg:inline text-sm text-blue-50 font-medium tabular-nums">{{ currentTime }}</span>
                        <button
                            @click="handleLogout"
                            class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 border border-white/30 rounded-lg font-semibold text-xs text-white tracking-wide transition"
                        >
                            ログアウト
                        </button>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-white/10 focus:outline-none transition"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div
                :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                class="sm:hidden bg-blue-700 pb-3"
            >
                <div class="pt-2 space-y-1 px-2">
                    <Link
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        :class="[
                            'block px-3 py-2 rounded-lg text-base font-medium',
                            route().current(item.pattern)
                                ? 'bg-white/20 text-white'
                                : 'text-blue-100 hover:bg-white/10'
                        ]"
                    >
                        {{ item.label }}
                    </Link>
                </div>
                <div class="pt-3 px-4 border-t border-white/20 mt-2">
                    <div v-if="authUser" class="text-sm text-white font-medium mb-1">👤 {{ authUser.name }}</div>
                    <div class="text-sm text-blue-100 mb-2">{{ currentTime }}</div>
                    <button
                        @click="handleLogout"
                        class="block w-full text-center px-3 py-2 rounded-lg text-base font-medium text-white bg-white/15 hover:bg-white/25 border border-white/30"
                    >
                        ログアウト
                    </button>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow-sm border-b border-slate-200" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
