<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          スタッフメンバー詳細: {{ staffMember.name }}
        </h2>
        <div class="flex gap-2">
          <Link
            :href="route('admin.staff-members.index')"
            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ← 一覧に戻る
          </Link>
          <Link
            :href="route('admin.staff-members.edit', staffMember.id)"
            class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
          >
            編集
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- 基本情報 -->
              <div>
                <h3 class="text-lg font-semibold text-slate-800 mb-4">スタッフメンバー情報</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                  <div>
                    <dt class="text-sm font-medium text-slate-500">スタッフID</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ staffMember.id }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-slate-500">ユーザーID</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ staffMember.user_id }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-slate-500">登録日</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ formatDate(staffMember.created_at) }}</dd>
                  </div>
                </dl>
              </div>

              <!-- ユーザー情報 -->
              <div>
                <h3 class="text-lg font-semibold text-slate-800 mb-4">ユーザー情報</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-slate-500">氏名</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ staffMember.user.name }}</dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-slate-500">メールアドレス</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ staffMember.user.email }}</dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-slate-500">社員番号</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ staffMember.user.emp_no }}</dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-slate-500">グループ</dt>
                    <dd class="mt-1">
                      <Badge variant="info">
                        {{ staffMember.user.group_id ? `グループ${staffMember.user.group_id}` : '未設定' }}
                      </Badge>
                    </dd>
                  </div>
                  <div v-if="staffMember.user">
                    <dt class="text-sm font-medium text-slate-500">管理者権限</dt>
                    <dd class="mt-1">
                      <Badge :variant="staffMember.user.is_admin ? 'danger' : 'neutral'">
                        {{ staffMember.user.is_admin ? '管理者' : '一般' }}
                      </Badge>
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
import Badge from '@/Components/UI/Badge.vue';

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
