<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">プロジェクトグループ編集</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- エラーメッセージ -->
            <div v-if="form.hasErrors" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
              <strong class="font-bold">エラーが発生しました</strong>
              <ul class="mt-2 list-disc list-inside">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
              </ul>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">基本情報</h3>

            <!-- グループ名 -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                グループ名 <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="例: プロジェクトA"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <!-- 説明 -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                説明
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="このプロジェクトグループの説明を入力してください"
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <!-- メンバー選択 -->
            <div class="border-t border-gray-300 pt-4">
              <h4 class="text-md font-semibold text-gray-900 mb-3">メンバー選択</h4>
              <p class="text-sm text-gray-600 mb-4">
                このプロジェクトグループに所属するメンバーを選択してください。
              </p>

              <!-- 検索 -->
              <div class="mb-3">
                <input
                  v-model="searchQuery"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="メンバーを検索..."
                />
              </div>

              <!-- ユーザー一覧 -->
              <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-md bg-white">
                <label
                  v-for="user in filteredUsers"
                  :key="user.id"
                  class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="form.user_ids"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3"
                  />
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                    <div v-if="user.email" class="text-xs text-gray-500">{{ user.email }}</div>
                  </div>
                </label>
              </div>
              <p v-if="form.errors.user_ids" class="mt-1 text-sm text-red-600">{{ form.errors.user_ids }}</p>

              <!-- 選択済みメンバー数 -->
              <div class="mt-3 text-sm text-gray-600">
                選択済み: {{ form.user_ids.length }}名
              </div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between pt-4">
              <Link
                :href="route('admin.project-groups.index')"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md disabled:opacity-50 font-semibold"
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

