<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    groups: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

// ログイン状態（localStorage は管理画面の user_id と衝突しないよう専用キーを使う）
const STORAGE_KEY = 'timeclock_user_id';
const loggedInUser = ref(null);

// ログインフォーム
const selectedGroupId = ref('');
const selectedUserId = ref('');
const errorMessage = ref('');
const processing = ref(false);

// 打刻状態
const status = ref('not_clocked_in'); // not_clocked_in / working / clocked_out
const clockInAt = ref(null);
const clockOutAt = ref(null);
const punchMessage = ref('');
const punchError = ref('');

// 誤操作防止：打刻前の確認ダイアログ（'clock-in' | 'clock-out' | null）
const confirmAction = ref(null);

// 現在時刻表示
const now = ref(new Date());
let clockTimer = null;
let statusTimer = null;
let toastTimer = null;

const currentTime = computed(() => {
    const h = String(now.value.getHours()).padStart(2, '0');
    const m = String(now.value.getMinutes()).padStart(2, '0');
    return `${h}:${m}`;
});
const currentSeconds = computed(() => String(now.value.getSeconds()).padStart(2, '0'));
const currentDate = computed(() =>
    now.value.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' })
);
const currentWeekday = computed(() =>
    now.value.toLocaleDateString('ja-JP', { weekday: 'long' })
);

const statusLabel = computed(() => ({
    not_clocked_in: '未出勤',
    working: '出勤中',
    clocked_out: '退勤済み',
}[status.value] ?? '不明'));

// 出勤ボタン：未出勤のときのみ押下可
const canClockIn = computed(() => status.value === 'not_clocked_in');
// 退勤ボタン：出勤中のときのみ押下可
const canClockOut = computed(() => status.value === 'working');

// 部署で絞り込んだスタッフ
const filteredUsers = computed(() => {
    if (!selectedGroupId.value) return props.users;
    return props.users.filter((u) => String(u.group_id) === String(selectedGroupId.value));
});

const onGroupChange = () => {
    if (selectedUserId.value && !filteredUsers.value.some((u) => String(u.id) === String(selectedUserId.value))) {
        selectedUserId.value = '';
    }
};

// ログイン
const login = async () => {
    errorMessage.value = '';
    if (!selectedUserId.value) {
        errorMessage.value = 'スタッフを選択してください。';
        return;
    }
    processing.value = true;
    try {
        const userId = parseInt(selectedUserId.value);
        const response = await axios.post('/api/timeclock/login', { user_id: userId });
        if (response.data.success) {
            localStorage.setItem(STORAGE_KEY, String(userId));
            loggedInUser.value = response.data.user;
            await fetchMyStatus();
        }
    } catch (error) {
        errorMessage.value =
            error.response?.data?.errors?.user_id?.[0] ||
            error.response?.data?.message ||
            'ログインに失敗しました。';
    } finally {
        processing.value = false;
    }
};

// ログアウト
const logout = () => {
    localStorage.removeItem(STORAGE_KEY);
    loggedInUser.value = null;
    selectedGroupId.value = '';
    selectedUserId.value = '';
    status.value = 'not_clocked_in';
    clockInAt.value = null;
    clockOutAt.value = null;
    punchMessage.value = '';
    punchError.value = '';
    confirmAction.value = null;
};

// 自分の当日状況を取得
const fetchMyStatus = async () => {
    if (!loggedInUser.value) return;
    try {
        const response = await axios.get('/api/timeclock/me', {
            params: { user_id: loggedInUser.value.id },
        });
        status.value = response.data.status;
        clockInAt.value = response.data.clock_in_at;
        clockOutAt.value = response.data.clock_out_at;
    } catch (error) {
        // ユーザーが無効になっていた場合はログアウトする
        if (error.response?.status === 404) logout();
    }
};

// 打刻ボタン押下 → まず確認ダイアログを開く（誤操作防止）
const requestPunch = (type) => {
    if (processing.value) return;
    if (type === 'clock-in' && !canClockIn.value) return;
    if (type === 'clock-out' && !canClockOut.value) return;
    punchMessage.value = '';
    punchError.value = '';
    confirmAction.value = type;
};

// 確認ダイアログで「確定」→ 打刻実行
const executePunch = async () => {
    const type = confirmAction.value;
    if (!type || !loggedInUser.value || processing.value) return;
    processing.value = true;
    try {
        const response = await axios.post(`/api/timeclock/${type}`, {
            user_id: loggedInUser.value.id,
        });
        status.value = response.data.status;
        clockInAt.value = response.data.clock_in_at;
        clockOutAt.value = response.data.clock_out_at;
        punchMessage.value = response.data.message;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => (punchMessage.value = ''), 5000);
    } catch (error) {
        punchError.value = error.response?.data?.message || '打刻に失敗しました。';
        // サーバー側の最新状態に同期する
        await fetchMyStatus();
    } finally {
        processing.value = false;
        confirmAction.value = null;
    }
};

// 既にログイン済みなら復元
const restoreLogin = async () => {
    const savedUserId = localStorage.getItem(STORAGE_KEY);
    if (!savedUserId) return;
    try {
        const response = await axios.post('/api/timeclock/login', { user_id: parseInt(savedUserId) });
        if (response.data.success) {
            loggedInUser.value = response.data.user;
            await fetchMyStatus();
        }
    } catch {
        localStorage.removeItem(STORAGE_KEY);
    }
};

onMounted(() => {
    clockTimer = setInterval(() => (now.value = new Date()), 1000);
    // 日付跨ぎや他端末での打刻とズレないよう、定期的にサーバー状態へ同期する
    statusTimer = setInterval(fetchMyStatus, 60000);
    restoreLogin();
});

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer);
    if (statusTimer) clearInterval(statusTimer);
    if (toastTimer) clearTimeout(toastTimer);
});
</script>

<template>
    <Head title="出退勤打刻" />

    <div class="tc-bg relative min-h-screen flex flex-col items-center justify-center overflow-hidden px-4 py-8 sm:py-12">
        <!-- 背景装飾：メタリックな光沢 -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-40 left-1/2 h-96 w-[60rem] -translate-x-1/2 rounded-full bg-white/[0.04] blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-zinc-400/[0.05] blur-3xl"></div>
        </div>

        <main class="relative w-full max-w-md">
            <!-- ブランド -->
            <header class="mb-5 flex items-center justify-between px-1">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-b from-zinc-100 to-zinc-400 shadow-lg shadow-black/40">
                        <span class="text-sm font-black tracking-tight text-zinc-900">A</span>
                    </div>
                    <div class="leading-tight">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-zinc-400">Akioka</p>
                        <p class="text-sm font-bold tracking-wider text-zinc-100">TIME CLOCK</p>
                    </div>
                </div>
                <a
                    href="/timeclock/status"
                    class="rounded-lg border border-zinc-700/80 bg-zinc-900/60 px-3 py-2 text-xs font-semibold text-zinc-300 backdrop-blur transition hover:border-zinc-500 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50"
                >
                    本日の状況 →
                </a>
            </header>

            <!-- メインカード -->
            <section class="overflow-hidden rounded-2xl border border-zinc-700/60 bg-gradient-to-b from-zinc-800/90 to-zinc-900/95 shadow-2xl shadow-black/60 ring-1 ring-white/[0.06] backdrop-blur">
                <!-- 時計 -->
                <div class="border-b border-zinc-700/60 bg-black/30 px-6 py-6 text-center sm:px-8">
                    <p class="text-xs font-medium tracking-[0.2em] text-zinc-400">
                        {{ currentDate }}<span class="ml-2 text-zinc-500">{{ currentWeekday }}</span>
                    </p>
                    <p class="mt-2 text-6xl font-bold tabular-nums tracking-tight text-white sm:text-7xl">
                        {{ currentTime }}<span class="ml-1 align-baseline text-2xl font-semibold text-zinc-500 sm:text-3xl">{{ currentSeconds }}</span>
                    </p>
                </div>

                <!-- ログインフォーム -->
                <div v-if="!loggedInUser" class="px-6 py-7 sm:px-8">
                    <h1 class="text-sm font-bold tracking-widest text-zinc-200">LOGIN</h1>
                    <p class="mt-1 text-xs text-zinc-500">部署とスタッフ名を選択してください</p>

                    <form @submit.prevent="login" class="mt-6 space-y-5">
                        <div>
                            <label for="group-select" class="mb-1.5 block text-xs font-semibold tracking-wider text-zinc-400">部署</label>
                            <select
                                id="group-select"
                                v-model="selectedGroupId"
                                @change="onGroupChange"
                                class="w-full rounded-lg border-zinc-600/80 bg-zinc-900/80 py-3 text-sm text-zinc-100 shadow-inner focus:border-zinc-300 focus:ring-zinc-300/40"
                            >
                                <option value="">すべての部署</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">
                                    {{ group.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="user-select" class="mb-1.5 block text-xs font-semibold tracking-wider text-zinc-400">スタッフ</label>
                            <select
                                id="user-select"
                                v-model="selectedUserId"
                                class="w-full rounded-lg border-zinc-600/80 bg-zinc-900/80 py-3 text-sm text-zinc-100 shadow-inner focus:border-zinc-300 focus:ring-zinc-300/40"
                            >
                                <option value="">選択してください</option>
                                <option v-for="user in filteredUsers" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>

                        <p v-if="errorMessage" role="alert" class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm text-red-300">
                            {{ errorMessage }}
                        </p>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full rounded-xl bg-gradient-to-b from-white to-zinc-200 py-3.5 text-sm font-bold tracking-widest text-zinc-900 shadow-lg shadow-black/40 transition hover:from-zinc-100 hover:to-zinc-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:scale-[0.98] disabled:opacity-40"
                        >
                            {{ processing ? 'ログイン中…' : 'ログイン' }}
                        </button>
                    </form>
                </div>

                <!-- 打刻画面 -->
                <div v-else class="px-6 py-6 sm:px-8">
                    <!-- ユーザー情報とステータス -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-lg font-bold text-white">{{ loggedInUser.name }}</p>
                            <div class="mt-1.5 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold tracking-wider"
                                :class="{
                                    'border-zinc-600 bg-zinc-800 text-zinc-400': status === 'not_clocked_in',
                                    'border-white/40 bg-white/10 text-white': status === 'working',
                                    'border-zinc-500 bg-zinc-700/60 text-zinc-300': status === 'clocked_out',
                                }"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full"
                                    :class="{
                                        'bg-zinc-500': status === 'not_clocked_in',
                                        'tc-pulse bg-white': status === 'working',
                                        'bg-zinc-400': status === 'clocked_out',
                                    }"
                                ></span>
                                {{ statusLabel }}
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="logout"
                            class="shrink-0 rounded-lg border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-400 transition hover:border-zinc-500 hover:text-zinc-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50"
                        >
                            ログアウト
                        </button>
                    </div>

                    <!-- 当日の打刻記録 -->
                    <dl class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-zinc-700/70 bg-black/30 px-4 py-3.5">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">出勤 In</dt>
                            <dd class="mt-1 text-2xl font-bold tabular-nums" :class="clockInAt ? 'text-white' : 'text-zinc-600'">
                                {{ clockInAt ?? '--:--' }}
                            </dd>
                        </div>
                        <div class="rounded-xl border border-zinc-700/70 bg-black/30 px-4 py-3.5">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">退勤 Out</dt>
                            <dd class="mt-1 text-2xl font-bold tabular-nums" :class="clockOutAt ? 'text-white' : 'text-zinc-600'">
                                {{ clockOutAt ?? '--:--' }}
                            </dd>
                        </div>
                    </dl>

                    <!-- フィードバック -->
                    <p v-if="punchMessage" role="status" class="mt-4 flex items-center gap-2 rounded-lg border border-white/20 bg-white/[0.07] px-4 py-2.5 text-sm font-medium text-white">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                        </svg>
                        {{ punchMessage }}
                    </p>
                    <p v-if="punchError" role="alert" class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm text-red-300">
                        {{ punchError }}
                    </p>

                    <!-- 出勤・退勤ボタン -->
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4">
                        <button
                            type="button"
                            @click="requestPunch('clock-in')"
                            :disabled="!canClockIn || processing"
                            :aria-disabled="!canClockIn || processing"
                            class="group rounded-2xl py-6 text-center transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:py-7"
                            :class="canClockIn && !processing
                                ? 'bg-gradient-to-b from-white to-zinc-200 text-zinc-900 shadow-lg shadow-black/50 hover:from-zinc-100 hover:to-zinc-300 active:scale-[0.97]'
                                : 'cursor-not-allowed border border-zinc-700/70 bg-zinc-800/50 text-zinc-600'"
                        >
                            <span class="block text-xl font-black tracking-widest sm:text-2xl">出勤</span>
                            <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.25em]"
                                :class="canClockIn && !processing ? 'text-zinc-500' : 'text-zinc-700'">Clock In</span>
                        </button>
                        <button
                            type="button"
                            @click="requestPunch('clock-out')"
                            :disabled="!canClockOut || processing"
                            :aria-disabled="!canClockOut || processing"
                            class="group rounded-2xl py-6 text-center transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:py-7"
                            :class="canClockOut && !processing
                                ? 'border border-zinc-400/60 bg-gradient-to-b from-zinc-600 to-zinc-800 text-white shadow-lg shadow-black/50 hover:from-zinc-500 hover:to-zinc-700 active:scale-[0.97]'
                                : 'cursor-not-allowed border border-zinc-700/70 bg-zinc-800/50 text-zinc-600'"
                        >
                            <span class="block text-xl font-black tracking-widest sm:text-2xl">退勤</span>
                            <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.25em]"
                                :class="canClockOut && !processing ? 'text-zinc-400' : 'text-zinc-700'">Clock Out</span>
                        </button>
                    </div>

                    <!-- 状態ガイド -->
                    <p class="mt-4 text-center text-[11px] leading-relaxed text-zinc-500">
                        <template v-if="status === 'not_clocked_in'">出勤ボタンで本日の勤務を開始します</template>
                        <template v-else-if="status === 'working'">勤務中は退勤ボタンのみ操作できます</template>
                        <template v-else>本日の勤務は終了しました。お疲れさまでした</template>
                    </p>
                </div>
            </section>

            <p class="mt-5 text-center text-[10px] tracking-[0.3em] text-zinc-600">AKIOKA RECEPTION SYSTEM</p>
        </main>

        <!-- 打刻確認ダイアログ（誤操作防止） -->
        <Transition name="tc-fade">
            <div
                v-if="confirmAction"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-6 backdrop-blur-sm"
                role="alertdialog"
                aria-modal="true"
                :aria-label="confirmAction === 'clock-in' ? '出勤の確認' : '退勤の確認'"
                @click.self="confirmAction = null"
            >
                <div class="w-full max-w-xs rounded-2xl border border-zinc-600/70 bg-gradient-to-b from-zinc-800 to-zinc-900 p-6 shadow-2xl ring-1 ring-white/10">
                    <p class="text-center text-4xl">{{ confirmAction === 'clock-in' ? '🌅' : '🌙' }}</p>
                    <p class="mt-3 text-center text-lg font-bold text-white">
                        {{ confirmAction === 'clock-in' ? '出勤を記録します' : '退勤を記録します' }}
                    </p>
                    <p class="mt-1 text-center text-sm tabular-nums text-zinc-400">{{ currentTime }} 現在</p>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            @click="confirmAction = null"
                            :disabled="processing"
                            class="rounded-xl border border-zinc-600 py-3 text-sm font-bold text-zinc-300 transition hover:border-zinc-400 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/50 active:scale-[0.97]"
                        >
                            キャンセル
                        </button>
                        <button
                            type="button"
                            @click="executePunch"
                            :disabled="processing"
                            class="rounded-xl bg-gradient-to-b from-white to-zinc-200 py-3 text-sm font-black text-zinc-900 shadow-lg transition hover:from-zinc-100 hover:to-zinc-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:scale-[0.97] disabled:opacity-50"
                        >
                            {{ processing ? '記録中…' : '確定する' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* メタリックな黒背景（微細なグリッドで新社屋のシステマチックな印象に） */
.tc-bg {
    background-color: #0b0b0d;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
        radial-gradient(ellipse at top, rgba(120, 125, 135, 0.18), transparent 60%);
    background-size: 44px 44px, 44px 44px, 100% 100%;
}

/* 出勤中インジケーターの点滅 */
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

/* 確認ダイアログのフェード */
.tc-fade-enter-active,
.tc-fade-leave-active {
    transition: opacity 0.15s ease;
}
.tc-fade-enter-from,
.tc-fade-leave-to {
    opacity: 0;
}
</style>
