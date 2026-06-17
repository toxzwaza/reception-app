<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">施設管理</h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <!-- ヘッダー -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">施設一覧</h3>
            <Link
              :href="route('admin.facilities.create')"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-semibold"
            >
              ＋ 新規登録
            </Link>
          </div>

          <!-- エラー表示 -->
          <div v-if="$page.props.errors?.facility" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ $page.props.errors.facility }}
          </div>

          <!-- テーブル -->
          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">施設名</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlook会議室</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">予約数</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="facility in facilities" :key="facility.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ facility.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">
                    <span v-if="facility.outlook_resource_email" class="inline-flex items-center gap-1">
                      <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                      {{ facility.outlook_resource_email }}
                    </span>
                    <span v-else class="text-gray-400">未連携</span>
                  </td>
                  <td class="px-4 py-3 text-sm text-center text-gray-600">{{ facility.schedule_events_count }}</td>
                  <td class="px-4 py-3 text-sm text-right space-x-2">
                    <Link
                      :href="route('admin.facilities.edit', facility.id)"
                      class="text-indigo-600 hover:text-indigo-800 font-medium"
                    >
                      編集
                    </Link>
                    <button
                      type="button"
                      @click="destroy(facility)"
                      class="text-red-600 hover:text-red-800 font-medium"
                    >
                      削除
                    </button>
                  </td>
                </tr>
                <tr v-if="facilities.length === 0">
                  <td colspan="4" class="px-4 py-8 text-center text-gray-500">施設が登録されていません。</td>
                </tr>
              </tbody>
            </table>
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
  facilities: { type: Array, default: () => [] },
});

const destroy = (facility) => {
  if (confirm(`施設「${facility.name}」を削除しますか？`)) {
    router.delete(route('admin.facilities.destroy', facility.id), {
      preserveScroll: true,
    });
  }
};
</script>
