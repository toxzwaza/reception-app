<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          事前アポイント一覧
        </h2>
        <Link
          :href="route('admin.appointments.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規登録
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 検索フォーム -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6">
          <form @submit.prevent="search" class="flex flex-wrap gap-3">
            <input
              v-model="form.search"
              type="text"
              placeholder="会社名・訪問者名・受付番号で検索"
              class="flex-1 min-w-[200px] rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <input
              v-model="form.date"
              type="date"
              class="rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
              検索
            </button>
            <button
              type="button"
              @click="clearFilters"
              class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
            >
              クリア
            </button>
          </form>
        </div>

        <!-- アポイント一覧 -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">受付番号</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">会社名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">訪問者名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">担当スタッフ</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">訪問日時</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">状態</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="appointment in appointments.data" :key="appointment.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                    {{ appointment.reception_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ appointment.company_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ appointment.visitor_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ appointment.staff_member?.name || '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ formatDate(appointment.visit_date) }}<br />
                    <span class="text-blue-700 font-medium">{{ appointment.visit_time }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="appointment.is_checked_in ? 'success' : 'warning'" dot>
                      {{ appointment.is_checked_in ? 'チェックイン済み' : '未チェックイン' }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right space-x-3">
                    <Link :href="route('admin.appointments.edit', appointment.id)" class="text-blue-600 hover:text-blue-800">
                      編集
                    </Link>
                    <button @click="deleteAppointment(appointment.id)" class="text-rose-600 hover:text-rose-800">
                      削除
                    </button>
                  </td>
                </tr>
                <tr v-if="appointments.data.length === 0">
                  <td colspan="7" class="px-6 py-10 text-center text-slate-400">アポイントがありません。</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ページネーション -->
          <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link
                  v-if="appointments.prev_page_url"
                  :href="appointments.prev_page_url"
                  class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50"
                >
                  前へ
                </Link>
                <Link
                  v-if="appointments.next_page_url"
                  :href="appointments.next_page_url"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50"
                >
                  次へ
                </Link>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-slate-600">
                    <span class="font-semibold">{{ appointments.from }}</span>
                    -
                    <span class="font-semibold">{{ appointments.to }}</span>
                    件 / 全
                    <span class="font-semibold">{{ appointments.total }}</span>
                    件
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px">
                    <Link
                      v-for="link in appointments.links"
                      :key="link.label"
                      :href="link.url"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        link.active
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-slate-300 text-slate-500 hover:bg-slate-50'
                      ]"
                      v-html="link.label"
                    />
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
  appointments: Object,
  filters: Object,
});

const form = ref({
  search: props.filters.search || '',
  date: props.filters.date || '',
});

const search = () => {
  router.get(route('admin.appointments.index'), form.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  form.value = { search: '', date: '' };
  router.get(route('admin.appointments.index'));
};

const deleteAppointment = (id) => {
  if (confirm('本当に削除しますか？')) {
    router.delete(route('admin.appointments.destroy', id));
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  });
};
</script>
