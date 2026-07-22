<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          集荷依頼管理
        </h2>
        <Link
          :href="route('admin.pickup-requests.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規登録
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div v-if="$page.props.flash?.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
          {{ $page.props.flash.success }}
        </div>

        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">依頼者</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">物品</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">置き場所</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">問い合わせ先</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">状態</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="req in pickupRequests" :key="req.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">{{ req.requester_name }}</td>
                  <td class="px-6 py-4 text-sm text-slate-700">{{ req.item }}</td>
                  <td class="px-6 py-4 text-sm text-slate-700">{{ req.storage_location || '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ req.contact_phone || '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="req.status === 'completed' ? 'success' : 'warning'" dot>
                      {{ req.status === 'completed' ? '集荷済み' : '未集荷' }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                    <div class="flex justify-end gap-3">
                      <Link :href="route('admin.pickup-requests.slip', req.id)" class="text-slate-600 hover:text-slate-800">伝票</Link>
                      <Link :href="route('admin.pickup-requests.edit', req.id)" class="text-blue-600 hover:text-blue-800">編集</Link>
                      <button @click="destroy(req)" class="text-rose-600 hover:text-rose-800">削除</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="pickupRequests.length === 0">
                  <td colspan="6" class="px-6 py-10 text-center text-slate-400">集荷依頼がありません。</td>
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
  pickupRequests: { type: Array, default: () => [] },
});

const destroy = (req) => {
  if (confirm(`「${req.requester_name} / ${req.item}」の集荷依頼を削除しますか？`)) {
    router.delete(route('admin.pickup-requests.destroy', req.id));
  }
};
</script>
