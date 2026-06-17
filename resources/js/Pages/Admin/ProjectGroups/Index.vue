<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          プロジェクトグループ管理
        </h2>
        <Link
          :href="route('admin.project-groups.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規登録
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 成功メッセージ -->
        <div v-if="$page.props.flash?.success" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
          {{ $page.props.flash.success }}
        </div>

        <!-- プロジェクトグループ一覧 -->
        <div v-if="projectGroups.length > 0" class="space-y-4">
          <div
            v-for="projectGroup in projectGroups"
            :key="projectGroup.id"
            class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-slate-800 mb-1">{{ projectGroup.name }}</h3>
                <p v-if="projectGroup.description" class="text-slate-600 text-sm mb-3">{{ projectGroup.description }}</p>
                <div class="flex items-center text-sm text-slate-500 mb-3">
                  <svg class="w-4 h-4 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  メンバー: {{ projectGroup.users?.length || 0 }}名
                </div>
                <div v-if="projectGroup.users && projectGroup.users.length > 0" class="flex flex-wrap gap-1.5">
                  <Badge v-for="user in projectGroup.users" :key="user.id" variant="purple">{{ user.name }}</Badge>
                </div>
              </div>

              <div class="flex gap-2 ml-4 flex-shrink-0">
                <Link
                  :href="route('admin.project-groups.edit', projectGroup.id)"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                >
                  編集
                </Link>
                <button
                  @click="deleteProjectGroup(projectGroup)"
                  class="bg-rose-50 hover:bg-rose-100 text-rose-600 px-4 py-2 rounded-lg text-sm font-medium transition"
                >
                  削除
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- データがない場合 -->
        <div v-else class="bg-white rounded-2xl border border-slate-200 shadow-sm text-center py-12">
          <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-slate-700">プロジェクトグループがありません</h3>
          <div class="mt-6">
            <Link :href="route('admin.project-groups.create')" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
              ＋ 新規登録
            </Link>
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

defineProps({
  projectGroups: Array,
});

const deleteProjectGroup = (projectGroup) => {
  if (confirm(`「${projectGroup.name}」を削除してもよろしいですか？`)) {
    router.delete(route('admin.project-groups.destroy', projectGroup.id));
  }
};
</script>
