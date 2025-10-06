<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">集荷伝票管理</h2>
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
            <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

        <!-- 集荷伝票一覧 -->
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
                      <button @click="sortBy('picked_up_at')" class="hover:text-gray-700">
                        集荷日時
                        <span v-if="filters.sort_by === 'picked_up_at'" class="ml-1">
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
                  <tr v-for="pickup in pickups.data" :key="pickup.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ pickup.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(pickup.picked_up_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <span :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        pickup.sealed_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                      ]">
                        {{ pickup.sealed_at ? '電子印済み' : '未押印' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link 
                        :href="route('admin.pickups.show', pickup.id)"
                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                      >
                        詳細
                      </Link>
                      <button 
                        v-if="!pickup.sealed_at"
                        @click="applySeal(pickup.id)"
                        class="text-green-600 hover:text-green-900"
                      >
                        電子印押下
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- ページネーション -->
            <div class="mt-6 flex justify-between items-center">
              <div class="text-sm text-gray-700">
                {{ pickups.from }} - {{ pickups.to }} / {{ pickups.total }}件
              </div>
              <div class="flex space-x-2">
                <Link 
                  v-for="link in pickups.links" 
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
  pickups: Object,
  filters: Object,
});

const filters = reactive({
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  sort_by: props.filters.sort_by || 'picked_up_at',
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
  router.get(route('admin.pickups.index'), filters, {
    preserveState: true,
    preserveScroll: true,
  });
};

// フィルタークリア
const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    if (key === 'sort_by') {
      filters[key] = 'picked_up_at';
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
const applySeal = async (pickupId) => {
  if (confirm('電子印を押下しますか？')) {
    try {
      const response = await fetch(route('admin.pickups.apply-seal', pickupId), {
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
        router.reload({ only: ['pickups'] });
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
