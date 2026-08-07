<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    groups: { type: Array, default: () => [] },
});

const loading = ref(true);
const errorMessage = ref('');
const date = ref('');
const count = ref({ total: 0, working: 0, clocked_out: 0 });
const attendances = ref([]);
const selectedGroupId = ref('');
const selectedStatus = ref(''); // '' | 'working' | 'clocked_out'
const nameQuery = ref('');
const lastUpdatedAt = ref(null);
let refreshTimer = null;

const groupNames = computed(() =>
    Object.fromEntries(props.groups.map((g) => [String(g.id), g.name]))
);

// 名前検索用の正規化（空白除去・小文字化で「秋岡 義典」「あきおか」等の表記ゆれを吸収）
const normalize = (text) => String(text ?? '').replace(/[\s　]/g, '').toLowerCase();

const filteredAttendances = computed(() => {
    const query = normalize(nameQuery.value);
    return attendances.value.filter((a) => {
        if (selectedGroupId.value && String(a.group_id) !== String(selectedGroupId.value)) return false;
        if (selectedStatus.value && a.status !== selectedStatus.value) return false;
        if (query && !normalize(a.name).includes(query) && !normalize(a.emp_no).includes(query)) return false;
        return true;
    });
});

const isFiltered = computed(() =>
    Boolean(selectedGroupId.value || selectedStatus.value || nameQuery.value.trim())
);

const clearFilters = () => {
    selectedGroupId.value = '';
    selectedStatus.value = '';
    nameQuery.value = '';
};

const statusOptions = [
    { value: '', label: 'すべて' },
    { value: 'working', label: '出勤中' },
    { value: 'clocked_out', label: '退勤済み' },
];

const displayDate = computed(() => {
    if (!date.value) return '';
    return new Date(`${date.value}T00:00:00`).toLocaleDateString('ja-JP', {
        year: 'numeric', month: 'long', day: 'numeric', weekday: 'long',
    });
});

const statusLabel = (status) => ({
    working: '出勤中',
    clocked_out: '退勤済み',
}[status] ?? status);

const fetchToday = async () => {
    errorMessage.value = '';
    try {
        const response = await axios.get('/api/attendances/today');
        date.value = response.data.date;
        count.value = response.data.count;
        attendances.value = response.data.attendances;
        lastUpdatedAt.value = new Date().toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });
    } catch {
        errorMessage.value = '出退勤情報の取得に失敗しました。';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchToday();
    // 1分ごとに自動更新
    refreshTimer = setInterval(fetchToday, 60000);
});

onUnmounted(() => {
    if (refreshTimer) clearInterval(refreshTimer);
});
</script>

<template>
    <Head title="本日の出退勤状況" />

    <div class="tc-bg min-h-screen text-zinc-100">
        <!-- ヘッダー -->
        <header class="sticky top-0 z-10 border-b border-zinc-700/60 bg-zinc-900/85 backdrop-blur">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-b from-zinc-100 to-zinc-400 shadow-lg shadow-black/40">
                        <span class="text-sm font-black tracking-tight text-zinc-900">A</span>
                    </div>
                    <div class="min-w-0 leading-tight">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-zinc-400">Attendance</p>
                        <h1 class="truncate text-sm font-bold tracking-wider text-white sm:text-base">本日の出退勤状況</h1>
                    </div>
                </div>
                <a
                    href="/timeclock"
                    class="shrink-0 rounded-lg border border-zinc-700/80 bg-zinc-900/60 px-3 py-2 text-xs font-semibold text-zinc-300 transition hover:border-zinc-500 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50"
                >
                    ← 打刻画面
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
            <!-- 日付・更新時刻 -->
            <p class="text-xs tracking-wider text-zinc-400">
                {{ displayDate }}
                <span v-if="lastUpdatedAt" class="ml-2 text-zinc-600">最終更新 {{ lastUpdatedAt }}・1分ごとに自動更新</span>
            </p>

            <!-- サマリー -->
            <dl class="mt-4 grid grid-cols-3 gap-3 sm:gap-4">
                <div class="rounded-xl border border-zinc-700/60 bg-gradient-to-b from-zinc-800/90 to-zinc-900/90 px-4 py-4 text-center ring-1 ring-white/[0.05] sm:py-5">
                    <dt class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500 sm:text-xs">出勤者計</dt>
                    <dd class="mt-1 text-2xl font-bold tabular-nums text-white sm:text-3xl">
                        {{ count.total }}<span class="ml-0.5 text-xs font-normal text-zinc-500">名</span>
                    </dd>
                </div>
                <div class="rounded-xl border border-white/25 bg-gradient-to-b from-zinc-800/90 to-zinc-900/90 px-4 py-4 text-center ring-1 ring-white/10 sm:py-5">
                    <dt class="flex items-center justify-center gap-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-300 sm:text-xs">
                        <span class="tc-pulse h-1.5 w-1.5 rounded-full bg-white"></span>出勤中
                    </dt>
                    <dd class="mt-1 text-2xl font-bold tabular-nums text-white sm:text-3xl">
                        {{ count.working }}<span class="ml-0.5 text-xs font-normal text-zinc-500">名</span>
                    </dd>
                </div>
                <div class="rounded-xl border border-zinc-700/60 bg-gradient-to-b from-zinc-800/90 to-zinc-900/90 px-4 py-4 text-center ring-1 ring-white/[0.05] sm:py-5">
                    <dt class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500 sm:text-xs">退勤済み</dt>
                    <dd class="mt-1 text-2xl font-bold tabular-nums text-zinc-300 sm:text-3xl">
                        {{ count.clocked_out }}<span class="ml-0.5 text-xs font-normal text-zinc-500">名</span>
                    </dd>
                </div>
            </dl>

            <!-- 絞り込みバー -->
            <div class="mt-6 flex flex-col gap-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- 名前・社員番号検索 -->
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.45 4.4l3.07 3.08a1 1 0 0 1-1.41 1.41l-3.08-3.07A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                        </svg>
                        <input
                            v-model="nameQuery"
                            type="search"
                            aria-label="名前・社員番号で検索"
                            placeholder="名前・社員番号で検索"
                            class="w-full rounded-lg border-zinc-600/80 bg-zinc-900/80 py-2 pl-9 pr-9 text-sm text-zinc-100 shadow-inner placeholder:text-zinc-600 focus:border-zinc-300 focus:ring-zinc-300/40"
                        />
                        <button
                            v-if="nameQuery"
                            type="button"
                            @click="nameQuery = ''"
                            aria-label="検索をクリア"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1 text-zinc-500 transition hover:text-zinc-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    </div>

                    <!-- 部署選択 -->
                    <select
                        v-model="selectedGroupId"
                        aria-label="部署で絞り込み"
                        class="rounded-lg border-zinc-600/80 bg-zinc-900/80 py-2 text-sm text-zinc-100 shadow-inner focus:border-zinc-300 focus:ring-zinc-300/40"
                    >
                        <option value="">すべての部署</option>
                        <option v-for="group in groups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- ステータス切替（セグメントコントロール） -->
                    <div role="radiogroup" aria-label="ステータスで絞り込み" class="inline-flex rounded-lg border border-zinc-700/80 bg-zinc-900/80 p-1">
                        <button
                            v-for="option in statusOptions"
                            :key="option.value"
                            type="button"
                            role="radio"
                            :aria-checked="selectedStatus === option.value"
                            @click="selectedStatus = option.value"
                            class="rounded-md px-3.5 py-1.5 text-xs font-bold tracking-wider transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50"
                            :class="selectedStatus === option.value
                                ? 'bg-gradient-to-b from-white to-zinc-200 text-zinc-900 shadow'
                                : 'text-zinc-400 hover:text-zinc-200'"
                        >
                            {{ option.label }}
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            v-if="isFiltered"
                            type="button"
                            @click="clearFilters"
                            class="rounded-lg px-3 py-2 text-xs font-semibold text-zinc-500 underline decoration-zinc-700 underline-offset-2 transition hover:text-zinc-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50"
                        >
                            絞り込み解除
                        </button>
                        <button
                            type="button"
                            @click="fetchToday"
                            class="rounded-lg border border-zinc-700/80 bg-zinc-900/60 px-4 py-2 text-xs font-semibold text-zinc-300 transition hover:border-zinc-500 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50 active:scale-[0.97]"
                        >
                            再読み込み
                        </button>
                    </div>
                </div>

                <!-- 絞り込み結果件数 -->
                <p v-if="isFiltered && !loading" class="text-xs text-zinc-500" role="status">
                    {{ filteredAttendances.length }} 件が該当（全 {{ attendances.length }} 件中）
                </p>
            </div>

            <p v-if="errorMessage" role="alert" class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm text-red-300">
                {{ errorMessage }}
            </p>

            <!-- 一覧 -->
            <section class="mt-4 overflow-hidden rounded-xl border border-zinc-700/60 bg-gradient-to-b from-zinc-800/70 to-zinc-900/80 ring-1 ring-white/[0.05]">
                <div v-if="loading" class="px-6 py-14 text-center text-sm text-zinc-500">読み込み中…</div>
                <div v-else-if="filteredAttendances.length === 0" class="px-6 py-14 text-center text-sm text-zinc-500">
                    <template v-if="isFiltered">
                        絞り込み条件に該当する記録がありません
                        <button
                            type="button"
                            @click="clearFilters"
                            class="ml-2 text-zinc-300 underline decoration-zinc-600 underline-offset-2 transition hover:text-white"
                        >
                            絞り込み解除
                        </button>
                    </template>
                    <template v-else>本日の出勤記録はまだありません</template>
                </div>

                <template v-else>
                    <!-- デスクトップ：テーブル -->
                    <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-700/60 bg-black/25 text-left text-[11px] uppercase tracking-[0.15em] text-zinc-500">
                                <th class="px-5 py-3 font-semibold">社員番号</th>
                                <th class="px-5 py-3 font-semibold">名前</th>
                                <th class="px-5 py-3 font-semibold">部署</th>
                                <th class="px-5 py-3 text-center font-semibold">出勤</th>
                                <th class="px-5 py-3 text-center font-semibold">退勤</th>
                                <th class="px-5 py-3 text-center font-semibold">ステータス</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            <tr v-for="attendance in filteredAttendances" :key="attendance.user_id" class="transition hover:bg-white/[0.03]">
                                <td class="px-5 py-3.5 tabular-nums text-zinc-500">{{ attendance.emp_no }}</td>
                                <td class="px-5 py-3.5 font-semibold text-zinc-100">{{ attendance.name }}</td>
                                <td class="px-5 py-3.5 text-zinc-400">{{ groupNames[String(attendance.group_id)] ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-center tabular-nums text-zinc-200">{{ attendance.clock_in_at ?? '--:--' }}</td>
                                <td class="px-5 py-3.5 text-center tabular-nums text-zinc-200">{{ attendance.clock_out_at ?? '--:--' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold tracking-wider"
                                        :class="attendance.status === 'working'
                                            ? 'border-white/40 bg-white/10 text-white'
                                            : 'border-zinc-600 bg-zinc-800/80 text-zinc-400'"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="attendance.status === 'working' ? 'tc-pulse bg-white' : 'bg-zinc-500'"
                                        ></span>
                                        {{ statusLabel(attendance.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>

                    <!-- モバイル：カード -->
                    <ul class="divide-y divide-zinc-800 md:hidden">
                        <li v-for="attendance in filteredAttendances" :key="attendance.user_id" class="px-4 py-3.5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-zinc-100">{{ attendance.name }}</p>
                                    <p class="mt-0.5 text-[11px] text-zinc-500">
                                        {{ attendance.emp_no }} ・ {{ groupNames[String(attendance.group_id)] ?? '—' }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold tracking-wider"
                                    :class="attendance.status === 'working'
                                        ? 'border-white/40 bg-white/10 text-white'
                                        : 'border-zinc-600 bg-zinc-800/80 text-zinc-400'"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="attendance.status === 'working' ? 'tc-pulse bg-white' : 'bg-zinc-500'"
                                    ></span>
                                    {{ statusLabel(attendance.status) }}
                                </span>
                            </div>
                            <div class="mt-2 flex gap-4 text-xs tabular-nums text-zinc-400">
                                <span><span class="mr-1 text-[10px] uppercase tracking-wider text-zinc-600">In</span>{{ attendance.clock_in_at ?? '--:--' }}</span>
                                <span><span class="mr-1 text-[10px] uppercase tracking-wider text-zinc-600">Out</span>{{ attendance.clock_out_at ?? '--:--' }}</span>
                            </div>
                        </li>
                    </ul>
                </template>
            </section>

            <p class="mt-6 text-center text-[10px] tracking-[0.3em] text-zinc-600">AKIOKA RECEPTION SYSTEM</p>
        </main>
    </div>
</template>

<style scoped>
/* メタリックな黒背景（打刻画面と共通のトーン） */
.tc-bg {
    background-color: #0b0b0d;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
        radial-gradient(ellipse at top, rgba(120, 125, 135, 0.18), transparent 60%);
    background-size: 44px 44px, 44px 44px, 100% 100%;
}

/* 検索クリアは自作の×ボタンを使うため、ブラウザ標準のクリアボタンは非表示にする */
input[type='search']::-webkit-search-cancel-button {
    -webkit-appearance: none;
    appearance: none;
}

.tc-pulse {
    animation: tc-pulse 1.6s ease-in-out infinite;
}
@keyframes tc-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.25; }
}
@media (prefers-reduced-motion: reduce) {
    .tc-pulse { animation: none; }
}
</style>
