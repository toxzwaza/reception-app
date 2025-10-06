<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">スタッフメンバー管理</h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('admin.dashboard')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            ダッシュボードに戻る
          </Link>
          <Link 
            :href="route('admin.staff-members.create')"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            新規登録
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- スタッフメンバー一覧 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      氏名
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      メールアドレス
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      部署
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Teams ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      登録日
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      操作
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="staff in staffMembers.data" :key="staff.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ staff.user?.name || 'N/A' }}</div>
                      <div v-if="staff.user" class="text-sm text-gray-500">社員番号: {{ staff.user.emp_no }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ staff.user?.email || 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ staff.user?.group_id ? `グループ${staff.user.group_id}` : '未設定' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ staff.user?.email || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(staff.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link 
                          :href="route('admin.staff-members.show', staff.id)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          詳細
                        </Link>
                        <Link 
                          :href="route('admin.staff-members.edit', staff.id)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          編集
                        </Link>
                        <button 
                          @click="deleteStaff(staff.id)"
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

            <!-- ページネーション -->
            <div class="mt-6 flex justify-between items-center">
              <div class="text-sm text-gray-700">
                {{ staffMembers.from }} - {{ staffMembers.to }} / {{ staffMembers.total }}件
              </div>
              <div class="flex space-x-2">
                <Link 
                  v-for="link in staffMembers.links" 
                  :key="link.label"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm leading-tight border rounded-lg',
                    link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-100'
                  ]"
                >
                  <span v-html="link.label"></span>
                </Link>
              </div>
            </div>

            <!-- 空の状態 -->
            <div v-if="staffMembers.data.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">スタッフメンバーが登録されていません</h3>
              <p class="mt-1 text-sm text-gray-500">新しいスタッフメンバーを登録してください。</p>
              <div class="mt-6">
                <Link 
                  :href="route('admin.staff-members.create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                  新規登録
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
  staffMembers: Object,
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

// スタッフ削除
const deleteStaff = (staffId) => {
  if (confirm('このスタッフメンバーを削除しますか？\n\nこの操作は取り消せません。')) {
    router.delete(route('admin.staff-members.destroy', staffId));
  }
};
</script>
