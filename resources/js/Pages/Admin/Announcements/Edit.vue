<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">お知らせ 編集</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- タイトル -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                タイトル <span class="text-red-500">*</span>
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                {{ form.errors.title }}
              </p>
            </div>

            <!-- 内容 -->
            <div>
              <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                内容 <span class="text-red-500">*</span>
              </label>
              <textarea
                id="content"
                v-model="form.content"
                rows="6"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              ></textarea>
              <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                {{ form.errors.content }}
              </p>
            </div>

            <!-- 種別 -->
            <div>
              <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                種別 <span class="text-red-500">*</span>
              </label>
              <select
                id="type"
                v-model="form.type"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="info">情報</option>
                <option value="warning">警告</option>
                <option value="error">エラー</option>
              </select>
              <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">
                {{ form.errors.type }}
              </p>
            </div>

            <!-- 表示開始日 -->
            <div>
              <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                表示開始日 <span class="text-red-500">*</span>
              </label>
              <input
                id="start_date"
                v-model="form.start_date"
                type="date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.start_date }}
              </p>
            </div>

            <!-- 表示終了日 -->
            <div>
              <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                表示終了日 <span class="text-red-500">*</span>
              </label>
              <input
                id="end_date"
                v-model="form.end_date"
                type="date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.end_date }}
              </p>
            </div>

            <!-- 表示順 -->
            <div>
              <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">
                表示順
              </label>
              <input
                id="display_order"
                v-model.number="form.display_order"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p class="mt-1 text-sm text-gray-500">小さい数字ほど上に表示されます</p>
              <p v-if="form.errors.display_order" class="mt-1 text-sm text-red-600">
                {{ form.errors.display_order }}
              </p>
            </div>

            <!-- 有効/無効 -->
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-700">
                有効
              </label>
            </div>

            <!-- ボタン -->
            <div class="flex justify-end space-x-3 pt-4">
              <Link 
                :href="route('admin.announcements.index')" 
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md disabled:opacity-50"
              >
                更新
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  announcement: Object,
});

const form = useForm({
  title: props.announcement.title,
  content: props.announcement.content,
  type: props.announcement.type,
  start_date: props.announcement.start_date,
  end_date: props.announcement.end_date,
  is_active: props.announcement.is_active,
  display_order: props.announcement.display_order,
});

const submit = () => {
  form.put(route('admin.announcements.update', props.announcement.id));
};
</script>



