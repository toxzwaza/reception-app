<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          スタッフメンバー管理
        </h2>
        <Link
          :href="route('admin.staff-members.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規登録
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">氏名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">メールアドレス</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">部署</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">登録日</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="staff in staffMembers.data" :key="staff.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-slate-800">{{ staff.user?.name || 'N/A' }}</div>
                    <div v-if="staff.user" class="text-xs text-slate-500">社員番号: {{ staff.user.emp_no }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ staff.user?.email || 'N/A' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge variant="info">{{ staff.user?.group_id ? `グループ${staff.user.group_id}` : '未設定' }}</Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ formatDate(staff.created_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right space-x-3">
                    <Link :href="route('admin.staff-members.show', staff.id)" class="text-slate-500 hover:text-slate-700">詳細</Link>
                    <Link :href="route('admin.staff-members.edit', staff.id)" class="text-blue-600 hover:text-blue-800">編集</Link>
                    <button @click="deleteStaff(staff.id)" class="text-rose-600 hover:text-rose-800">削除</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ページネーション -->
          <div v-if="staffMembers.data.length > 0" class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
            <div class="flex justify-between items-center">
              <div class="text-sm text-slate-600">
                {{ staffMembers.from }} - {{ staffMembers.to }} / {{ staffMembers.total }}件
              </div>
              <div class="flex gap-2">
                <Link
                  v-for="link in staffMembers.links"
                  :key="link.label"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm leading-tight border rounded-lg',
                    link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'
                  ]"
                >
                  <span v-html="link.label"></span>
                </Link>
              </div>
            </div>
          </div>

          <!-- 空の状態 -->
          <div v-if="staffMembers.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-slate-700">スタッフメンバーが登録されていません</h3>
            <div class="mt-6">
              <Link :href="route('admin.staff-members.create')" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                ＋ 新規登録
              </Link>
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
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
  staffMembers: Object,
});

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('ja-JP', {
    year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit',
  });
};

const deleteStaff = (staffId) => {
  if (confirm('このスタッフメンバーを削除しますか？\n\nこの操作は取り消せません。')) {
    router.delete(route('admin.staff-members.destroy', staffId));
  }
};
</script>
