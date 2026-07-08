<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          スタッフメンバー登録
        </h2>
        <Link
          :href="route('admin.staff-members.index')"
          class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
        >
          ← 一覧に戻る
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6">
              <!-- 基本情報 -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">スタッフメンバー登録</h3>
                <div class="grid grid-cols-1 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                      ユーザー選択 <span class="text-rose-500">*</span>
                    </label>
                    <select
                      v-model="form.user_id"
                      class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                      <option value="">ユーザーを選択してください</option>
                      <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                        {{ user.name }} ({{ user.email }}) - {{ user.emp_no }}
                      </option>
                    </select>
                    <div v-if="errors.user_id" class="mt-1 text-sm text-rose-600">{{ errors.user_id }}</div>
                    <p class="mt-2 text-sm text-slate-500">
                      選択したユーザーの基本情報（氏名、メールアドレス等）は、Userテーブルから自動的に取得されます。
                    </p>
                  </div>
                </div>
              </div>

              <!-- 送信ボタン -->
              <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                <Link
                  :href="route('admin.staff-members.index')"
                  class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
                >
                  キャンセル
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition"
                >
                  {{ processing ? '登録中...' : '登録' }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  availableUsers: Array,
  errors: Object,
});

const form = reactive({
  user_id: '',
});

const processing = ref(false);

// フォーム送信
const submit = () => {
  processing.value = true;
  
  router.post(route('admin.staff-members.store'), form, {
    onFinish: () => {
      processing.value = false;
    }
  });
};
</script>
