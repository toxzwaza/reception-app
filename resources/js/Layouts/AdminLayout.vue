<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

// モバイル時のサイドバー開閉状態
const sidebarOpen = ref(false);

// ログイン中のユーザー
const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);

// ログアウト：localStorageのuser_idをクリアしてからログアウト（再ログイン防止）
const handleLogout = () => {
    localStorage.removeItem('user_id');
    router.post(route('logout'));
};

// ナビゲーション項目（Heroicons outline のパスをアイコンとして保持）
const navItems = [
    {
        label: 'ダッシュボード', route: 'admin.dashboard', pattern: 'admin.dashboard',
        icon: 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    },
    {
        label: 'アポイント', route: 'admin.appointments.index', pattern: 'admin.appointments.*',
        icon: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
    },
    {
        label: 'プロジェクトグループ', route: 'admin.project-groups.index', pattern: 'admin.project-groups.*',
        icon: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    },
    {
        label: '納品書', route: 'admin.deliveries.index', pattern: 'admin.deliveries.*',
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
    },
    {
        label: '集荷伝票', route: 'admin.pickups.index', pattern: 'admin.pickups.*',
        icon: 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375c-.621 0-1.125-.504-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-4.5m0 0h-3.375m3.375 0V9.75c0-.621-.504-1.125-1.125-1.125h-2.25m0 0V5.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h1.5m0-5.25h16.5',
    },
];

const isActive = (item) => route().current(item.pattern);

// ルート遷移でモバイルサイドバーを閉じる
const closeSidebar = () => { sidebarOpen.value = false; };

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
        <!-- モバイル用オーバーレイ -->
        <transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                @click="closeSidebar"
                class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm md:hidden"
            ></div>
        </transition>

        <!-- サイドバー -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-white border-r border-slate-200 transition-transform duration-300 md:translate-x-0',
                sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full',
            ]"
        >
            <!-- ロゴ -->
            <div class="flex h-16 items-center gap-2.5 px-5 border-b border-slate-200">
                <Link :href="route('admin.dashboard')" @click="closeSidebar" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 text-white shadow-sm shadow-blue-600/30">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75V21" />
                        </svg>
                    </span>
                    <span class="text-[15px] font-bold leading-tight tracking-tight text-slate-800">
                        受付管理システム
                    </span>
                </Link>
            </div>

            <!-- ナビゲーション -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    @click="closeSidebar"
                    :class="[
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150',
                        isActive(item)
                            ? 'bg-blue-50 text-blue-700'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                    ]"
                >
                    <svg
                        class="h-5 w-5 shrink-0 transition-colors"
                        :class="isActive(item) ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    <span class="truncate">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- ユーザー情報 + ログアウト -->
            <div class="border-t border-slate-200 p-3">
                <div v-if="authUser" class="flex items-center gap-3 rounded-xl px-2 py-2">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-slate-800">{{ authUser.name }}</div>
                        <div class="text-xs text-slate-400">管理者</div>
                    </div>
                </div>
                <button
                    @click="handleLogout"
                    class="mt-1 flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-rose-50 hover:text-rose-600"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    ログアウト
                </button>
            </div>
        </aside>

        <!-- メインエリア（サイドバー分だけ右へ寄せる） -->
        <div class="md:pl-64">
            <!-- トップバー（スクロール追従） -->
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md sm:px-6 lg:px-8">
                <!-- モバイル用ハンバーガー -->
                <button
                    @click="sidebarOpen = true"
                    class="-ml-1 rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 md:hidden"
                    aria-label="メニューを開く"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- ページ見出し（各ページの header スロット） -->
                <div class="min-w-0 flex-1">
                    <slot name="header" />
                </div>

                <!-- 現在時刻 -->
                <span class="hidden whitespace-nowrap text-sm font-medium tabular-nums text-slate-400 lg:inline">
                    {{ currentTime }}
                </span>
            </header>

            <!-- ページコンテンツ -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
