<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    groups: { type: Array, default: () => [] },
    staffMembers: { type: Array, default: () => [] },
});

const selectedGroupId = ref('');
const selectedUserId = ref('');
const errorMessage = ref('');
const processing = ref(false);

// 部署で絞り込んだスタッフ
const filteredStaff = computed(() => {
    if (!selectedGroupId.value) return props.staffMembers;
    return props.staffMembers.filter((s) => String(s.group_id) === String(selectedGroupId.value));
});

// 部署を変えて選択中のスタッフが外れたら解除
const onGroupChange = () => {
    if (selectedUserId.value && !filteredStaff.value.some((s) => String(s.id) === String(selectedUserId.value))) {
        selectedUserId.value = '';
    }
};

// ログイン（部署→スタッフ選択。パスワードなし）
const submit = async () => {
    errorMessage.value = '';
    if (!selectedUserId.value) {
        errorMessage.value = 'スタッフを選択してください。';
        return;
    }
    processing.value = true;
    try {
        const userId = parseInt(selectedUserId.value);
        const response = await axios.post('/api/login-local', { user_id: userId });
        if (response.data.success) {
            localStorage.setItem('user_id', String(userId));
            await axios.post('/api/set-session-user', { user_id: userId }).catch(() => {});
            window.location.href = `/admin/dashboard?user_id=${userId}`;
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

// 既にlocalStorageにuser_idがあれば自動ログイン（ログアウト時はクリア済み）
const checkExistingUser = () => {
    const existingUserId = localStorage.getItem('user_id');
    if (existingUserId) {
        axios
            .post('/api/set-session-user', { user_id: parseInt(existingUserId) })
            .then(() => {
                window.location.href = `/admin/dashboard?user_id=${existingUserId}`;
            })
            .catch(() => {
                localStorage.removeItem('user_id');
            });
    }
};

onMounted(() => {
    checkExistingUser();
});
</script>

<template>
    <Head title="ログイン" />

    <div
        class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 px-4 py-10"
    >
        <!-- 背景の装飾 -->
        <div class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-20 w-[28rem] h-[28rem] rounded-full bg-indigo-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute top-1/3 right-1/4 w-72 h-72 rounded-full bg-blue-300/10 blur-3xl"></div>

        <div class="relative w-full max-w-md">
            <!-- カード -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden ring-1 ring-black/5">
                <!-- カードヘッダー -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-8 text-center">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-3xl shadow-inner">
                        🏢
                    </div>
                    <h1 class="mt-4 text-xl font-bold tracking-wide text-white">受付管理システム</h1>
                    <p class="mt-1 text-sm text-blue-100">管理画面ログイン</p>
                </div>

                <!-- フォーム -->
                <div class="px-8 py-8">
                    <div
                        v-if="status"
                        class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm font-medium text-green-700"
                    >
                        {{ status }}
                    </div>

                    <p class="mb-6 text-center text-sm text-slate-500">
                        部署とスタッフ名を選択してログインしてください
                    </p>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- 部署選択 -->
                        <div>
                            <label for="group-select" class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 mb-1.5">
                                <span class="text-blue-600">🏬</span> 部署
                            </label>
                            <select
                                id="group-select"
                                v-model="selectedGroupId"
                                @change="onGroupChange"
                                class="block w-full rounded-xl border-slate-300 bg-slate-50 text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition py-2.5"
                            >
                                <option value="">すべての部署</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">
                                    {{ group.name }}
                                </option>
                            </select>
                        </div>

                        <!-- スタッフ選択 -->
                        <div>
                            <label for="staff-select" class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 mb-1.5">
                                <span class="text-blue-600">👤</span> スタッフ名
                            </label>
                            <select
                                id="staff-select"
                                v-model="selectedUserId"
                                required
                                autofocus
                                class="block w-full rounded-xl border-slate-300 bg-slate-50 text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition py-2.5"
                            >
                                <option value="">選択してください（{{ filteredStaff.length }}名）</option>
                                <option v-for="staff in filteredStaff" :key="staff.id" :value="staff.id">
                                    {{ staff.name }}
                                </option>
                            </select>
                        </div>

                        <!-- エラー -->
                        <div
                            v-if="errorMessage"
                            class="rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm font-medium text-red-600"
                        >
                            {{ errorMessage }}
                        </div>

                        <!-- ログインボタン -->
                        <button
                            type="submit"
                            :disabled="processing"
                            class="group relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/30 transition hover:from-blue-700 hover:to-blue-800 hover:shadow-blue-700/40 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <svg
                                v-if="processing"
                                class="animate-spin h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>{{ processing ? 'ログイン中…' : 'ログイン' }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-blue-100/70">
                © 株式会社アキオカ 受付管理システム
            </p>
        </div>
    </div>
</template>
