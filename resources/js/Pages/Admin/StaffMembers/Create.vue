<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">スタッフメンバー登録</h2>
        <Link 
          :href="route('admin.staff-members.index')"
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
        >
          一覧に戻る
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <!-- 基本情報 -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">スタッフメンバー登録</h3>
                <div class="grid grid-cols-1 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      ユーザー選択 <span class="text-red-500">*</span>
                    </label>
                    <select 
                      v-model="form.user_id"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      required
                    >
                      <option value="">ユーザーを選択してください</option>
                      <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                        {{ user.name }} ({{ user.email }}) - {{ user.emp_no }}
                      </option>
                    </select>
                    <div v-if="errors.user_id" class="mt-1 text-sm text-red-600">{{ errors.user_id }}</div>
                    <p class="mt-2 text-sm text-gray-500">
                      選択したユーザーの基本情報（氏名、メールアドレス等）は、Userテーブルから自動的に取得されます。
                    </p>
                  </div>
                </div>
              </div>

              <!-- 送信ボタン -->
              <div class="flex justify-end space-x-4">
                <Link 
                  :href="route('admin.staff-members.index')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  キャンセル
                </Link>
                <button 
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
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
