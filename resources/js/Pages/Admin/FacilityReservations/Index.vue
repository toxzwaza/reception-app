<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          施設予約一覧
        </h2>
        <Link
          :href="route('admin.facility-reservations.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規予約
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 検索フォーム -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
          <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">施設</label>
              <select
                v-model="searchForm.facility_id"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="">すべて</option>
                <option v-for="facility in facilities" :key="facility.id" :value="facility.id">
                  {{ facility.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">日付</label>
              <input
                type="date"
                v-model="searchForm.date"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div class="flex items-end gap-2">
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex-1 font-medium transition">
                検索
              </button>
              <button type="button" @click="clearSearch" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg font-medium transition">
                クリア
              </button>
            </div>
          </form>
        </div>

        <!-- 予約一覧 -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">日付</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">時間</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">施設</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">タイトル</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">バッジ</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">参加者</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ formatDate(reservation.date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-700 font-medium">
                    {{ reservation.start_datetime }} - {{ reservation.end_datetime }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ reservation.facility?.name }}</td>
                  <td class="px-6 py-4 text-sm text-slate-800 font-medium">{{ reservation.title }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <Badge v-if="reservation.badge" variant="warning">{{ reservation.badge }}</Badge>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <div class="flex flex-wrap gap-1">
                      <Badge v-for="participant in reservation.participants.slice(0, 3)" :key="participant.id" variant="info">
                        {{ participant.name }}
                      </Badge>
                      <Badge v-if="reservation.participants.length > 3" variant="neutral">
                        +{{ reservation.participants.length - 3 }}
                      </Badge>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                    <Link :href="route('admin.facility-reservations.edit', reservation.id)" class="text-blue-600 hover:text-blue-800">
                      編集
                    </Link>
                    <button @click="deleteReservation(reservation.id)" class="text-rose-600 hover:text-rose-800">
                      削除
                    </button>
                  </td>
                </tr>
                <tr v-if="reservations.data.length === 0">
                  <td colspan="7" class="px-6 py-10 text-center text-slate-400">予約が見つかりませんでした</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ページネーション -->
          <div v-if="reservations.data.length > 0" class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-slate-600">
                全{{ reservations.total }}件中 {{ reservations.from }}-{{ reservations.to }}件を表示
              </div>
              <div class="flex gap-2">
                <Link
                  v-for="link in reservations.links"
                  :key="link.label"
                  :href="link.url"
                  v-html="link.label"
                  :class="[
                    'px-3 py-1 rounded-lg border',
                    link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50',
                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                  ]"
                  :disabled="!link.url"
                />
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
  reservations: Object,
  facilities: Array,
  filters: Object,
});

const searchForm = ref({
  facility_id: props.filters.facility_id || '',
  date: props.filters.date || '',
});

const search = () => {
  router.get(route('admin.facility-reservations.index'), searchForm.value, {
    preserveState: true,
    replace: true,
  });
};

const clearSearch = () => {
  searchForm.value = { facility_id: '', date: '' };
  search();
};

const deleteReservation = (id) => {
  if (confirm('この予約を削除してもよろしいですか？')) {
    router.delete(route('admin.facility-reservations.destroy', id));
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][date.getDay()];
  return `${year}/${month}/${day} (${dayOfWeek})`;
};
</script>
