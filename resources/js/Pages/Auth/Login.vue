<script setup>
import { ref, onMounted } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const users = ref([]);
const selectedUser = ref(null);
const isUserSelected = ref(false);

const form = useForm({
    user_id: '',
    password: '',
    remember: false,
});

// ユーザー一覧を取得
const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/users');
        users.value = response.data;
    } catch (error) {
        console.error('ユーザー一覧の取得に失敗しました:', error);
    }
};

// ユーザー選択
const selectUser = (user) => {
    console.log('ユーザー選択:', user);
    selectedUser.value = user;
    form.user_id = user.id.toString(); // 文字列として設定
    isUserSelected.value = true;
    console.log('form.user_id設定後:', form.user_id, typeof form.user_id);
};

// ユーザー選択をクリア
const clearUserSelection = () => {
    selectedUser.value = null;
    form.user_id = '';
    isUserSelected.value = false;
    form.reset('password');
};

// ログイン処理
const submit = async () => {
    if (!form.user_id) {
        form.setError('user_id', 'ユーザーを選択してください。');
        return;
    }

    if (!form.password) {
        form.setError('password', 'パスワードを入力してください。');
        return;
    }

    try {
        // localStorage認証APIを使用
        const loginData = {
            user_id: parseInt(form.user_id), // 整数に変換
            password: form.password,
        };
        
        console.log('ログインリクエスト送信:', {
            form_user_id: form.user_id,
            form_user_id_type: typeof form.user_id,
            parsed_user_id: loginData.user_id,
            parsed_user_id_type: typeof loginData.user_id,
            password: form.password,
        });
        
        const response = await axios.post('/api/login-local', loginData);

               console.log('ログインレスポンス:', response.data);
        console.log('response.data.success:', response.data.success);
        console.log('typeof response.data.success:', typeof response.data.success);

        if (response.data.success) {
            // ログイン成功時、localStorageにuser_idを保存
            const userIdToStore = loginData.user_id.toString();
            localStorage.setItem('user_id', userIdToStore);
            console.log('ログイン成功、localStorageにuser_idを保存:', userIdToStore);
            console.log('localStorage確認:', localStorage.getItem('user_id'));
            console.log('リダイレクト実行前...');
            
            // セッションにもuser_idを確実に保存してからリダイレクト
            axios.post('/api/set-session-user', {
                user_id: loginData.user_id
            }).then(() => {
                console.log('セッションにuser_idを保存完了、管理画面にリダイレクト');
                // 管理画面ダッシュボードにリダイレクト（クエリパラメータ付き）
                window.location.href = `/admin/dashboard?user_id=${loginData.user_id}`;
            }).catch(sessionError => {
                console.error('セッション保存エラー:', sessionError);
                // セッション保存に失敗した場合でもクエリパラメータでリダイレクト
                window.location.href = `/admin/dashboard?user_id=${loginData.user_id}`;
            });
        } else {
            console.error('ログイン失敗:', response.data);
        }
    } catch (error) {
        console.error('ログインエラー:', error);
        console.error('エラーレスポンス:', error.response);
        
        if (error.response && error.response.data.errors) {
            const errors = error.response.data.errors;
            console.log('バリデーションエラー:', errors);
            
            if (errors.password) {
                form.setError('password', errors.password[0]);
                
                // パスワードエラーの場合、テストAPIを呼び出してデバッグ情報を取得
                if (form.user_id && form.password) {
                    console.log('パスワードエラー発生、テストAPIを呼び出し中...');
                    axios.post('/api/test-password', {
                        user_id: parseInt(form.user_id),
                        password: form.password,
                    }).then(response => {
                        console.log('パスワードテスト結果:', response.data);
                    }).catch(testError => {
                        console.error('パスワードテストエラー:', testError);
                    });
                }
            }
            if (errors.user_id) {
                form.setError('user_id', errors.user_id[0]);
            }
        } else if (error.response && error.response.data.message) {
            console.log('エラーメッセージ:', error.response.data.message);
            form.setError('password', error.response.data.message);
        } else {
            form.setError('password', 'ログインに失敗しました。');
        }
    }
};

// 既存のuser_idをチェック
const checkExistingUser = () => {
    const existingUserId = localStorage.getItem('user_id');
    if (existingUserId) {
        // 既存のuser_idからユーザー情報を取得
        axios.get(`/api/users/${existingUserId}`)
            .then(response => {
                selectUser(response.data);
                // 既存のユーザーがいる場合は、セッションに保存して管理画面にリダイレクト
                axios.post('/api/set-session-user', {
                    user_id: parseInt(existingUserId)
                }).then(() => {
                    console.log('セッションにuser_idを保存完了、管理画面にリダイレクト');
                    window.location.href = `/admin/dashboard?user_id=${existingUserId}`;
                }).catch(sessionError => {
                    console.error('セッション保存エラー:', sessionError);
                    // セッション保存に失敗した場合でもクエリパラメータでリダイレクト
                    window.location.href = `/admin/dashboard?user_id=${existingUserId}`;
                });
            })
            .catch(error => {
                // ユーザーが見つからない場合、localStorageをクリア
                localStorage.removeItem('user_id');
                console.error('ユーザー情報の取得に失敗しました:', error);
            });
    }
};

onMounted(() => {
    fetchUsers();
    checkExistingUser();
});
</script>

<template>
    <GuestLayout>
        <Head title="ログイン" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <!-- ユーザー選択 -->
            <div v-if="!isUserSelected">
                <InputLabel for="user-select" value="ユーザーを選択してください" />
                
                <div class="mt-1 relative">
                    <select
                        id="user-select"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        @change="selectUser(users.find(u => u.id === parseInt($event.target.value)))"
                        required
                        autofocus
                    >
                        <option value="">ユーザーを選択してください</option>
                        <option
                            v-for="user in users"
                            :key="user.id"
                            :value="user.id.toString()"
                        >
                            {{ user.emp_no }} - {{ user.name }}
                        </option>
                    </select>
                </div>
                
                <InputError class="mt-2" :message="form.errors.user_id" />
            </div>

            <!-- 選択されたユーザー情報 -->
            <div v-if="isUserSelected" class="bg-gray-50 p-4 rounded-md">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-900">選択されたユーザー</p>
                        <p class="text-lg text-gray-700">{{ selectedUser.emp_no }} - {{ selectedUser.name }}</p>
                    </div>
                    <button
                        type="button"
                        @click="clearUserSelection"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        変更
                    </button>
                </div>
            </div>

            <div v-if="isUserSelected" class="mt-4">
                <InputLabel for="password" value="パスワード" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div v-if="isUserSelected" class="block mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-gray-600">ログイン状態を保持する</span>
                </label>
            </div>

            <div v-if="isUserSelected" class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    パスワードをお忘れですか？
                </Link>

                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    ログイン
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
