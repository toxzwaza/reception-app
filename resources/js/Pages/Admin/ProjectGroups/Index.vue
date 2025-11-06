<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">プロジェクトグループ管理</h2>
        <Link
          :href="route('admin.project-groups.create')"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md"
        >
          + 新規登録
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- 成功メッセージ -->
            <div
              v-if="$page.props.flash?.success"
              class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
            >
              {{ $page.props.flash.success }}
            </div>

            <!-- プロジェクトグループ一覧 -->
            <div v-if="projectGroups.length > 0" class="space-y-4">
              <div
                v-for="projectGroup in projectGroups"
                :key="projectGroup.id"
                class="border border-gray-300 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ projectGroup.name }}</h3>
                    <p v-if="projectGroup.description" class="text-gray-600 text-sm mb-3">
                      {{ projectGroup.description }}
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      メンバー: {{ projectGroup.users?.length || 0 }}名
                    </div>
                    
                    <!-- メンバー一覧 -->
                    <div v-if="projectGroup.users && projectGroup.users.length > 0" class="mt-3">
                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="user in projectGroup.users"
                          :key="user.id"
                          class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800 border border-indigo-300"
                        >
                          {{ user.name }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="flex gap-2 ml-4">
                    <Link
                      :href="route('admin.project-groups.edit', projectGroup.id)"
                      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
                    >
                      編集
                    </Link>
                    <button
                      @click="deleteProjectGroup(projectGroup)"
                      class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm"
                    >
                      削除
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- データがない場合 -->
            <div v-else class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">プロジェクトグループがありません</h3>
              <p class="mt-1 text-sm text-gray-500">新しいプロジェクトグループを登録してください。</p>
              <div class="mt-6">
                <Link
                  :href="route('admin.project-groups.create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                  + 新規登録
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

defineProps({
  projectGroups: Array,
});

const deleteProjectGroup = (projectGroup) => {
  if (confirm(`「${projectGroup.name}」を削除してもよろしいですか？`)) {
    router.delete(route('admin.project-groups.destroy', projectGroup.id));
  }
};
</script>

