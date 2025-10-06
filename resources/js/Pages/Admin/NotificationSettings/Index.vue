<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">通知設定管理</h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('admin.dashboard')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            ダッシュボードに戻る
          </Link>
          <Link 
            :href="route('admin.notification-settings.create')"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            新規作成
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 通知設定一覧 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      通知名
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      トリガーイベント
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      受信者数
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      状態
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      作成日
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      操作
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="setting in notificationSettings" :key="setting.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ setting.name }}</div>
                      <div v-if="setting.description" class="text-sm text-gray-500">{{ setting.description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ triggerEvents[setting.trigger_event] }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ setting.recipients.length }}名
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        setting.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]">
                        {{ setting.is_active ? '有効' : '無効' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(setting.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link 
                          :href="route('admin.notification-settings.show', setting.id)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          詳細
                        </Link>
                        <Link 
                          :href="route('admin.notification-settings.edit', setting.id)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          編集
                        </Link>
                        <button 
                          @click="toggleSetting(setting.id)"
                          :class="[
                            'text-sm',
                            setting.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'
                          ]"
                        >
                          {{ setting.is_active ? '無効化' : '有効化' }}
                        </button>
                        <button 
                          @click="deleteSetting(setting.id)"
                          class="text-red-600 hover:text-red-900"
                        >
                          削除
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 空の状態 -->
            <div v-if="notificationSettings.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7H4l5-5v5z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">通知設定がありません</h3>
              <p class="mt-1 text-sm text-gray-500">新しい通知設定を作成してください。</p>
              <div class="mt-6">
                <Link 
                  :href="route('admin.notification-settings.create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                  新規作成
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  notificationSettings: Array,
  triggerEvents: Object,
  notificationTypes: Object,
});

// 日付フォーマット
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// 通知設定の有効/無効切り替え
const toggleSetting = (settingId) => {
  if (confirm('通知設定の状態を変更しますか？')) {
    router.post(route('admin.notification-settings.toggle', settingId), {}, {
      preserveScroll: true,
    });
  }
};

// 通知設定の削除
const deleteSetting = (settingId) => {
  if (confirm('この通知設定を削除しますか？\n\nこの操作は取り消せません。')) {
    router.delete(route('admin.notification-settings.destroy', settingId));
  }
};
</script>
