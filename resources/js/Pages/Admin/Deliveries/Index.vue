<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">納品書・受領書管理</h2>
        <Link 
          :href="route('admin.dashboard')"
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
        >
          ダッシュボードに戻る
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 検索フィルター -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">書類種別</label>
                <select v-model="filters.delivery_type" class="w-full border-gray-300 rounded-md shadow-sm">
                  <option value="">すべて</option>
                  <option value="納品書">納品書</option>
                  <option value="受領書">受領書</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                <input 
                  type="date" 
                  v-model="filters.date_from" 
                  class="w-full border-gray-300 rounded-md shadow-sm"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                <input 
                  type="date" 
                  v-model="filters.date_to" 
                  class="w-full border-gray-300 rounded-md shadow-sm"
                >
              </div>
              <div class="flex items-end">
                <button 
                  type="submit"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2"
                >
                  検索
                </button>
                <button 
                  type="button"
                  @click="clearFilters"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  クリア
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- 納品書・受領書一覧 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <button @click="sortBy('id')" class="hover:text-gray-700">
                        ID
                        <span v-if="filters.sort_by === 'id'" class="ml-1">
                          {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
                        </span>
                      </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      書類種別
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <button @click="sortBy('received_at')" class="hover:text-gray-700">
                        受付日時
                        <span v-if="filters.sort_by === 'received_at'" class="ml-1">
                          {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
                        </span>
                      </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      電子印状態
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      操作
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="delivery in deliveries.data" :key="delivery.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ delivery.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <span :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        delivery.delivery_type === '納品書' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                      ]">
                        {{ delivery.delivery_type }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(delivery.received_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <span :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        delivery.sealed_document_image ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                      ]">
                        {{ delivery.sealed_document_image ? '電子印済み' : '未押印' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link 
                        :href="route('admin.deliveries.show', delivery.id)"
                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                      >
                        詳細
                      </Link>

                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- ページネーション -->
            <div class="mt-6 flex justify-between items-center">
              <div class="text-sm text-gray-700">
                {{ deliveries.from }} - {{ deliveries.to }} / {{ deliveries.total }}件
              </div>
              <div class="flex space-x-2">
                <Link 
                  v-for="link in deliveries.links" 
                  :key="link.label"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm leading-tight border rounded-lg',
                    link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-100'
                  ]"
                >
                  <span v-html="link.label"></span>
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
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  deliveries: Object,
  filters: Object,
});

const filters = reactive({
  delivery_type: props.filters.delivery_type || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  sort_by: props.filters.sort_by || 'received_at',
  sort_order: props.filters.sort_order || 'desc',
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

// フィルター適用
const applyFilters = () => {
  router.get(route('admin.deliveries.index'), filters, {
    preserveState: true,
    preserveScroll: true,
  });
};

// フィルタークリア
const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    if (key === 'sort_by') {
      filters[key] = 'received_at';
    } else if (key === 'sort_order') {
      filters[key] = 'desc';
    } else {
      filters[key] = '';
    }
  });
  applyFilters();
};

// ソート
const sortBy = (column) => {
  if (filters.sort_by === column) {
    filters.sort_order = filters.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    filters.sort_by = column;
    filters.sort_order = 'asc';
  }
  applyFilters();
};

// 電子印押下
const applySeal = async (deliveryId) => {
  if (confirm('電子印を押下しますか？')) {
    try {
      const response = await fetch(route('admin.deliveries.apply-seal', deliveryId), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          seal_type: 'approved',
          staff_member_id: 1 // 仮のスタッフID、実際は認証されたユーザーから取得
        })
      });

      const result = await response.json();

      if (result.success) {
        alert('電子印が正常に適用されました。');
        router.reload({ only: ['deliveries'] });
      } else {
        alert('電子印の適用に失敗しました: ' + result.message);
      }
    } catch (error) {
      console.error('電子印適用エラー:', error);
      alert('電子印の適用中にエラーが発生しました。');
    }
  }
};
</script>
