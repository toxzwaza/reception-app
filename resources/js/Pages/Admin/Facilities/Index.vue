<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          施設管理
        </h2>
        <Link
          :href="route('admin.facilities.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規登録
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- エラー表示 -->
        <div v-if="$page.props.errors?.facility" class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl">
          {{ $page.props.errors.facility }}
        </div>

        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">施設名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Outlook会議室</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">予約数</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="facility in facilities" :key="facility.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ facility.name }}</td>
                  <td class="px-6 py-4 text-sm">
                    <Badge v-if="facility.outlook_resource_email" variant="success" dot>
                      {{ facility.outlook_resource_email }}
                    </Badge>
                    <Badge v-else variant="neutral">未連携</Badge>
                  </td>
                  <td class="px-6 py-4 text-sm text-center text-slate-600">{{ facility.schedule_events_count }}</td>
                  <td class="px-6 py-4 text-sm text-right space-x-3">
                    <Link :href="route('admin.facilities.edit', facility.id)" class="text-blue-600 hover:text-blue-800 font-medium">
                      編集
                    </Link>
                    <button type="button" @click="destroy(facility)" class="text-rose-600 hover:text-rose-800 font-medium">
                      削除
                    </button>
                  </td>
                </tr>
                <tr v-if="facilities.length === 0">
                  <td colspan="4" class="px-6 py-10 text-center text-slate-400">施設が登録されていません。</td>
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
import Badge from '@/Components/UI/Badge.vue';

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
