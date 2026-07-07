<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          納品書・受領書管理
        </h2>
        <Link
          :href="route('admin.dashboard')"
          class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
        >
          ← ダッシュボード
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 検索フィルター -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6">
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">書類種別</label>
              <select v-model="filters.delivery_type" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">すべて</option>
                <option value="納品書">納品書</option>
                <option value="受領書">受領書</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">開始日</label>
              <input
                type="date"
                v-model="filters.date_from"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">終了日</label>
              <input
                type="date"
                v-model="filters.date_to"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
            </div>
            <div class="flex items-end gap-2">
              <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
              >
                検索
              </button>
              <button
                type="button"
                @click="clearFilters"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
              >
                クリア
              </button>
            </div>
          </form>
        </div>

        <!-- 納品書・受領書一覧 -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <button @click="sortBy('id')" class="inline-flex items-center gap-1 hover:text-slate-700">
                      ID
                      <span v-if="filters.sort_by === 'id'">{{ filters.sort_order === 'asc' ? '↑' : '↓' }}</span>
                    </button>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">書類種別</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <button @click="sortBy('received_at')" class="inline-flex items-center gap-1 hover:text-slate-700">
                      受付日時
                      <span v-if="filters.sort_by === 'received_at'">{{ filters.sort_order === 'asc' ? '↑' : '↓' }}</span>
                    </button>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">電子印状態</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="delivery in deliveries.data" :key="delivery.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                    {{ delivery.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="delivery.delivery_type === '納品書' ? 'info' : 'success'">
                      {{ delivery.delivery_type }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ formatDate(delivery.received_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="delivery.sealed_document_image ? 'success' : 'warning'" dot>
                      {{ delivery.sealed_document_image ? '電子印済み' : '未押印' }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                    <Link
                      :href="route('admin.deliveries.show', delivery.id)"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      詳細
                    </Link>
                  </td>
                </tr>
                <tr v-if="deliveries.data.length === 0">
                  <td colspan="5" class="px-6 py-10 text-center text-slate-400">該当する書類がありません。</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ページネーション -->
          <div class="flex justify-between items-center px-6 py-4 border-t border-slate-200">
            <div class="text-sm text-slate-600">
              {{ deliveries.from }} - {{ deliveries.to }} / 全 {{ deliveries.total }} 件
            </div>
            <div class="flex space-x-2">
              <Link
                v-for="link in deliveries.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm leading-tight border rounded-lg transition',
                  link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-500 border-slate-300 hover:bg-slate-50'
                ]"
              >
                <span v-html="link.label"></span>
              </Link>
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
import Badge from '@/Components/UI/Badge.vue';

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
