<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">施設予約一覧</h2>
        <Link
          :href="route('admin.facility-reservations.create')"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
        >
          新規予約
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 検索フォーム -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
          <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">施設</label>
              <select
                v-model="searchForm.facility_id"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">すべて</option>
                <option v-for="facility in facilities" :key="facility.id" :value="facility.id">
                  {{ facility.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">日付</label>
              <input
                type="date"
                v-model="searchForm.date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>

            <div class="flex items-end gap-2">
              <button
                type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md flex-1"
              >
                検索
              </button>
              <button
                type="button"
                @click="clearSearch"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md"
              >
                クリア
              </button>
            </div>
          </form>
        </div>

        <!-- 予約一覧 -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  日付
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  時間
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  施設
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  タイトル
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  バッジ
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  参加者
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  操作
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(reservation.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ reservation.start_datetime }} - {{ reservation.end_datetime }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ reservation.facility?.name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ reservation.title }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span v-if="reservation.badge" class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                    {{ reservation.badge }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="participant in reservation.participants.slice(0, 3)"
                      :key="participant.id"
                      class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded text-xs"
                    >
                      {{ participant.name }}
                    </span>
                    <span v-if="reservation.participants.length > 3" class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
                      +{{ reservation.participants.length - 3 }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('admin.facility-reservations.edit', reservation.id)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    編集
                  </Link>
                  <button
                    @click="deleteReservation(reservation.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    削除
                  </button>
                </td>
              </tr>
              <tr v-if="reservations.data.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  予約が見つかりませんでした
                </td>
              </tr>
            </tbody>
          </table>

          <!-- ページネーション -->
          <div v-if="reservations.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                全{{ reservations.total }}件中 {{ reservations.from }}-{{ reservations.to }}件を表示
              </div>
              <div class="flex gap-2">
                <Link
                  v-for="link in reservations.links"
                  :key="link.label"
                  :href="link.url"
                  v-html="link.label"
                  :class="[
                    'px-3 py-1 rounded',
                    link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
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
  searchForm.value = {
    facility_id: '',
    date: '',
  };
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

