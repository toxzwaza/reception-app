<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          お知らせ一覧
        </h2>
        <Link
          :href="route('admin.announcements.create')"
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
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">表示順</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">タイトル</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">種別</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">表示期間</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">状態</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="announcement in announcements.data" :key="announcement.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ announcement.display_order }}</td>
                  <td class="px-6 py-4 text-sm text-slate-800 font-medium">{{ announcement.title }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="typeVariant(announcement.type)">{{ typeLabel(announcement.type) }}</Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ announcement.start_date }}<br />〜 {{ announcement.end_date }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="announcement.is_active ? 'success' : 'neutral'" dot>
                      {{ announcement.is_active ? '有効' : '無効' }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right space-x-3">
                    <Link :href="route('admin.announcements.edit', announcement.id)" class="text-blue-600 hover:text-blue-800">編集</Link>
                    <button @click="deleteAnnouncement(announcement.id)" class="text-rose-600 hover:text-rose-800">削除</button>
                  </td>
                </tr>
                <tr v-if="announcements.data.length === 0">
                  <td colspan="6" class="px-6 py-10 text-center text-slate-400">お知らせがありません。</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ページネーション -->
          <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="hidden sm:block">
                <p class="text-sm text-slate-600">
                  <span class="font-semibold">{{ announcements.from }}</span> - <span class="font-semibold">{{ announcements.to }}</span>
                  件 / 全 <span class="font-semibold">{{ announcements.total }}</span> 件
                </p>
              </div>
              <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px">
                <Link
                  v-for="link in announcements.links"
                  :key="link.label"
                  :href="link.url"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-slate-300 text-slate-500 hover:bg-slate-50'
                  ]"
                  v-html="link.label"
                />
              </nav>
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

defineProps({
  announcements: Object,
});

const typeLabel = (type) => ({ info: '情報', warning: '警告', error: 'エラー' }[type] || type);
const typeVariant = (type) => (type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info');

const deleteAnnouncement = (id) => {
  if (confirm('本当に削除しますか？')) {
    router.delete(route('admin.announcements.destroy', id));
  }
};
</script>
