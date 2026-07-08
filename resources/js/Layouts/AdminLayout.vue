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

// 副ナビ（設定・マスタ系）
const secondaryNavItems = [
    {
        label: '施設予約', route: 'admin.facility-reservations.index', pattern: 'admin.facility-reservations.*',
        icon: 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z',
    },
    {
        label: '施設管理', route: 'admin.facilities.index', pattern: 'admin.facilities.*',
        icon: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    },
    {
        label: 'スタッフ', route: 'admin.staff-members.index', pattern: 'admin.staff-members.*',
        icon: 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
    },
    {
        label: '通知設定', route: 'admin.notification-settings.index', pattern: 'admin.notification-settings.*',
        icon: 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
    },
    {
        label: '部署電話番号', route: 'admin.departments.index', pattern: 'admin.departments.*',
        icon: 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z',
    },
    {
        label: 'お知らせ', route: 'admin.announcements.index', pattern: 'admin.announcements.*',
        icon: 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.51l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.34 1.125a23.74 23.74 0 0 1 1.202 4.463M19.48 8.588a24.014 24.014 0 0 1 1.052 4.463m0 0a48.309 48.309 0 0 0-2.244-.194c-1.86-.088-3.63.354-5.108 1.203m6.352-1.009c-.22-1.484-.578-2.925-1.052-4.463',
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

                <!-- 区切り：その他（設定・マスタ系） -->
                <div class="px-3 pb-2 pt-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">その他</span>
                </div>

                <Link
                    v-for="item in secondaryNavItems"
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
