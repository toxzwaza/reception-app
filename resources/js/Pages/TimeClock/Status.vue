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
const lastUpdatedAt = ref(null);
let refreshTimer = null;

const groupNames = computed(() =>
    Object.fromEntries(props.groups.map((g) => [String(g.id), g.name]))
);

const filteredAttendances = computed(() => {
    if (!selectedGroupId.value) return attendances.value;
    return attendances.value.filter((a) => String(a.group_id) === String(selectedGroupId.value));
});

const statusLabel = (status) => ({
    working: '出勤中',
    clocked_out: '退勤済み',
}[status] ?? status);

const statusClass = (status) => ({
    working: 'bg-green-100 text-green-700',
    clocked_out: 'bg-blue-100 text-blue-700',
}[status] ?? 'bg-slate-100 text-slate-600');

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

    <div class="min-h-screen bg-slate-100">
        <!-- ヘッダー -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-6 shadow">
            <div class="mx-auto max-w-4xl flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-white">本日の出退勤状況</h1>
                    <p class="mt-0.5 text-sm text-emerald-100">
                        {{ date }}
                        <span v-if="lastUpdatedAt" class="ml-2 text-emerald-200">（{{ lastUpdatedAt }} 更新）</span>
                    </p>
                </div>
                <a
                    href="/timeclock"
                    class="rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold text-white hover:bg-white/25 transition"
                >
                    打刻画面へ
                </a>
            </div>
        </div>

        <div class="mx-auto max-w-4xl px-6 py-6">
            <!-- サマリー -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-black/5">
                    <p class="text-sm text-slate-500">本日の出勤者</p>
                    <p class="mt-1 text-3xl font-bold text-slate-800">{{ count.total }}<span class="text-base font-normal text-slate-400"> 名</span></p>
                </div>
                <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-black/5">
                    <p class="text-sm text-slate-500">出勤中</p>
                    <p class="mt-1 text-3xl font-bold text-green-600">{{ count.working }}<span class="text-base font-normal text-slate-400"> 名</span></p>
                </div>
                <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-black/5">
                    <p class="text-sm text-slate-500">退勤済み</p>
                    <p class="mt-1 text-3xl font-bold text-blue-600">{{ count.clocked_out }}<span class="text-base font-normal text-slate-400"> 名</span></p>
                </div>
            </div>

            <!-- 絞り込み -->
            <div class="mt-6 flex items-center justify-between">
                <select
                    v-model="selectedGroupId"
                    class="rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                    <option value="">すべての部署</option>
                    <option v-for="group in groups" :key="group.id" :value="group.id">
                        {{ group.name }}
                    </option>
                </select>
                <button
                    type="button"
                    @click="fetchToday"
                    class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm ring-1 ring-black/5 hover:bg-slate-50 transition"
                >
                    再読み込み
                </button>
            </div>

            <div v-if="errorMessage" class="mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <!-- 一覧 -->
            <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-black/5">
                <div v-if="loading" class="px-6 py-10 text-center text-slate-400">読み込み中…</div>
                <div v-else-if="filteredAttendances.length === 0" class="px-6 py-10 text-center text-slate-400">
                    本日の出勤記録はまだありません
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-slate-500">
                            <th class="px-5 py-3 font-semibold">社員番号</th>
                            <th class="px-5 py-3 font-semibold">名前</th>
                            <th class="px-5 py-3 font-semibold">部署</th>
                            <th class="px-5 py-3 font-semibold text-center">出勤</th>
                            <th class="px-5 py-3 font-semibold text-center">退勤</th>
                            <th class="px-5 py-3 font-semibold text-center">ステータス</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="attendance in filteredAttendances" :key="attendance.user_id" class="hover:bg-slate-50">
                            <td class="px-5 py-3 tabular-nums text-slate-500">{{ attendance.emp_no }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ attendance.name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ groupNames[String(attendance.group_id)] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center tabular-nums">{{ attendance.clock_in_at ?? '--:--' }}</td>
                            <td class="px-5 py-3 text-center tabular-nums">{{ attendance.clock_out_at ?? '--:--' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block rounded-full px-3 py-0.5 text-xs font-semibold" :class="statusClass(attendance.status)">
                                    {{ statusLabel(attendance.status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
