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

// 現在時刻表示
const now = ref(new Date());
let clockTimer = null;

const currentTime = computed(() =>
    now.value.toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
);
const currentDate = computed(() =>
    now.value.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' })
);

const statusLabel = computed(() => ({
    not_clocked_in: '未出勤',
    working: '出勤中',
    clocked_out: '退勤済み',
}[status.value] ?? '不明'));

const statusClass = computed(() => ({
    not_clocked_in: 'bg-slate-100 text-slate-600',
    working: 'bg-green-100 text-green-700',
    clocked_out: 'bg-blue-100 text-blue-700',
}[status.value] ?? 'bg-slate-100 text-slate-600'));

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

// 打刻（type: 'clock-in' | 'clock-out'）
const punch = async (type) => {
    if (!loggedInUser.value || processing.value) return;
    punchMessage.value = '';
    punchError.value = '';
    processing.value = true;
    try {
        const response = await axios.post(`/api/timeclock/${type}`, {
            user_id: loggedInUser.value.id,
        });
        status.value = response.data.status;
        clockInAt.value = response.data.clock_in_at;
        clockOutAt.value = response.data.clock_out_at;
        punchMessage.value = response.data.message;
    } catch (error) {
        punchError.value = error.response?.data?.message || '打刻に失敗しました。';
        // サーバー側の最新状態に同期する
        await fetchMyStatus();
    } finally {
        processing.value = false;
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
    restoreLogin();
});

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer);
});
</script>

<template>
    <Head title="出退勤打刻" />

    <div
        class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-700 to-cyan-800 px-4 py-10"
    >
        <!-- 背景の装飾 -->
        <div class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-20 w-[28rem] h-[28rem] rounded-full bg-teal-300/20 blur-3xl"></div>

        <div class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden ring-1 ring-black/5">
                <!-- ヘッダー -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-8 text-center">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-3xl shadow-inner">
                        ⏰
                    </div>
                    <h1 class="mt-4 text-xl font-bold tracking-wide text-white">出退勤打刻</h1>
                    <p class="mt-1 text-sm text-emerald-100">{{ currentDate }}</p>
                    <p class="mt-2 text-4xl font-bold tabular-nums text-white">{{ currentTime }}</p>
                </div>

                <!-- ログインフォーム -->
                <div v-if="!loggedInUser" class="px-8 py-8">
                    <p class="mb-6 text-center text-sm text-slate-500">
                        部署とスタッフ名を選択してログインしてください
                    </p>

                    <form @submit.prevent="login" class="space-y-5">
                        <div>
                            <label for="group-select" class="block text-sm font-semibold text-slate-700 mb-1.5">部署</label>
                            <select
                                id="group-select"
                                v-model="selectedGroupId"
                                @change="onGroupChange"
                                class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="">すべての部署</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">
                                    {{ group.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="user-select" class="block text-sm font-semibold text-slate-700 mb-1.5">スタッフ</label>
                            <select
                                id="user-select"
                                v-model="selectedUserId"
                                class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="">選択してください</option>
                                <option v-for="user in filteredUsers" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>

                        <div v-if="errorMessage" class="rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700">
                            {{ errorMessage }}
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full rounded-lg bg-emerald-600 py-3 text-white font-semibold shadow hover:bg-emerald-700 transition disabled:opacity-50"
                        >
                            {{ processing ? 'ログイン中…' : 'ログイン' }}
                        </button>
                    </form>
                </div>

                <!-- 打刻画面 -->
                <div v-else class="px-8 py-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-bold text-slate-800">{{ loggedInUser.name }} さん</p>
                            <span
                                class="mt-1 inline-block rounded-full px-3 py-0.5 text-sm font-semibold"
                                :class="statusClass"
                            >
                                {{ statusLabel }}
                            </span>
                        </div>
                        <button
                            type="button"
                            @click="logout"
                            class="text-sm text-slate-400 hover:text-slate-600 underline"
                        >
                            ログアウト
                        </button>
                    </div>

                    <!-- 当日の打刻記録 -->
                    <div class="mt-5 grid grid-cols-2 gap-3 text-center">
                        <div class="rounded-lg bg-slate-50 border border-slate-200 py-3">
                            <p class="text-xs text-slate-500">出勤時刻</p>
                            <p class="mt-1 text-xl font-bold tabular-nums text-slate-800">{{ clockInAt ?? '--:--' }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 border border-slate-200 py-3">
                            <p class="text-xs text-slate-500">退勤時刻</p>
                            <p class="mt-1 text-xl font-bold tabular-nums text-slate-800">{{ clockOutAt ?? '--:--' }}</p>
                        </div>
                    </div>

                    <div v-if="punchMessage" class="mt-4 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm text-green-700">
                        {{ punchMessage }}
                    </div>
                    <div v-if="punchError" class="mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700">
                        {{ punchError }}
                    </div>

                    <!-- 出勤・退勤ボタン -->
                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <button
                            type="button"
                            @click="punch('clock-in')"
                            :disabled="!canClockIn || processing"
                            class="rounded-xl py-5 text-lg font-bold text-white shadow transition
                                   bg-emerald-600 hover:bg-emerald-700
                                   disabled:bg-slate-300 disabled:cursor-not-allowed"
                        >
                            出勤
                        </button>
                        <button
                            type="button"
                            @click="punch('clock-out')"
                            :disabled="!canClockOut || processing"
                            class="rounded-xl py-5 text-lg font-bold text-white shadow transition
                                   bg-rose-600 hover:bg-rose-700
                                   disabled:bg-slate-300 disabled:cursor-not-allowed"
                        >
                            退勤
                        </button>
                    </div>

                    <p class="mt-4 text-center text-xs text-slate-400">
                        退勤ボタンは「出勤中」のときのみ押せます
                    </p>
                </div>
            </div>

            <p class="mt-4 text-center">
                <a href="/timeclock/status" class="text-sm text-white/80 hover:text-white underline">
                    本日の出退勤状況を確認する
                </a>
            </p>
        </div>
    </div>
</template>
