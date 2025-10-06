<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          スタッフメンバー詳細: {{ staffMember.name }}
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('admin.staff-members.index')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            一覧に戻る
          </Link>
          <Link 
            :href="route('admin.staff-members.edit', staffMember.id)"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            編集
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- 基本情報 -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">スタッフメンバー情報</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">スタッフID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ staffMember.id }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">ユーザーID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ staffMember.user_id }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">登録日</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(staffMember.created_at) }}</dd>
                  </div>
                </dl>
              </div>

              <!-- ユーザー情報 -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">ユーザー情報</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-gray-500">氏名</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ staffMember.user.name }}</dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ staffMember.user.email }}</dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-gray-500">社員番号</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ staffMember.user.emp_no }}</dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-gray-500">グループ</dt>
                    <dd class="mt-1">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ staffMember.user.group_id ? `グループ${staffMember.user.group_id}` : '未設定' }}
                      </span>
                    </dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-gray-500">管理者権限</dt>
                    <dd class="mt-1">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="staffMember.user.is_admin ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'">
                        {{ staffMember.user.is_admin ? '管理者' : '一般' }}
                      </span>
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  staffMember: Object,
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
</script>
