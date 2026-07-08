<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        プロジェクトグループ編集
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- エラーメッセージ -->
            <div v-if="form.hasErrors" class="bg-rose-50 border border-rose-300 text-rose-700 px-4 py-3 rounded-lg relative mb-4">
              <strong class="font-bold">エラーが発生しました</strong>
              <ul class="mt-2 list-disc list-inside">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
              </ul>
            </div>

            <h3 class="text-lg font-semibold text-slate-800 border-b border-slate-200 pb-2">基本情報</h3>

            <!-- グループ名 -->
            <div>
              <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                グループ名 <span class="text-rose-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="例: プロジェクトA"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</p>
            </div>

            <!-- 説明 -->
            <div>
              <label for="description" class="block text-sm font-medium text-slate-700 mb-1">
                説明
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="このプロジェクトグループの説明を入力してください"
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</p>
            </div>

            <!-- メンバー選択 -->
            <div class="border-t border-slate-200 pt-4">
              <h4 class="text-md font-semibold text-slate-800 mb-3">メンバー選択</h4>
              <p class="text-sm text-slate-600 mb-4">
                このプロジェクトグループに所属するメンバーを選択してください。
              </p>

              <!-- 検索 -->
              <div class="mb-3">
                <input
                  v-model="searchQuery"
                  type="text"
                  class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  placeholder="メンバーを検索..."
                />
              </div>

              <!-- ユーザー一覧 -->
              <div class="max-h-64 overflow-y-auto border border-slate-300 rounded-lg bg-white">
                <label
                  v-for="user in filteredUsers"
                  :key="user.id"
                  class="flex items-center px-3 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-b-0"
                >
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="form.user_ids"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 mr-3"
                  />
                  <div class="flex-1">
                    <div class="font-medium text-slate-800">{{ user.name }}</div>
                    <div v-if="user.email" class="text-xs text-slate-500">{{ user.email }}</div>
                  </div>
                </label>
              </div>
              <p v-if="form.errors.user_ids" class="mt-1 text-sm text-rose-600">{{ form.errors.user_ids }}</p>

              <!-- 選択済みメンバー数 -->
              <div class="mt-3 text-sm text-slate-600">
                選択済み: {{ form.user_ids.length }}名
              </div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between pt-4 border-t border-slate-200">
              <Link
                :href="route('admin.project-groups.index')"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg disabled:opacity-50 font-semibold transition"
              >
                更新
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  projectGroup: Object,
  users: Array,
});

const form = useForm({
  name: props.projectGroup.name,
  description: props.projectGroup.description || '',
  user_ids: props.projectGroup.users?.map(u => u.id) || [],
});

const searchQuery = ref('');

const filteredUsers = computed(() => {
  if (!searchQuery.value) {
    return props.users;
  }
  
  const query = searchQuery.value.toLowerCase();
  return props.users.filter(user => {
    return user.name.toLowerCase().includes(query) || 
           (user.email && user.email.toLowerCase().includes(query));
  });
});

const submit = () => {
  console.log('Submitting form with data:', form.data());
  
  form.put(route('admin.project-groups.update', props.projectGroup.id), {
    onSuccess: (response) => {
      console.log('Success:', response);
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
      alert('更新に失敗しました。エラーメッセージを確認してください。');
    },
    onFinish: () => {
      console.log('Form submission finished');
    }
  });
};
</script>

