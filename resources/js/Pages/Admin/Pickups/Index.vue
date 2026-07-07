<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          集荷伝票管理
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
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

        <!-- 集荷伝票一覧 -->
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
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <button @click="sortBy('picked_up_at')" class="inline-flex items-center gap-1 hover:text-slate-700">
                      集荷日時
                      <span v-if="filters.sort_by === 'picked_up_at'">{{ filters.sort_order === 'asc' ? '↑' : '↓' }}</span>
                    </button>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">電子印状態</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="pickup in pickups.data" :key="pickup.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                    {{ pickup.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ formatDate(pickup.picked_up_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="pickup.sealed_at ? 'success' : 'warning'" dot>
                      {{ pickup.sealed_at ? '電子印済み' : '未押印' }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right space-x-3">
                    <Link
                      :href="route('admin.pickups.show', pickup.id)"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      詳細
                    </Link>
                    <button
                      v-if="!pickup.sealed_at"
                      @click="applySeal(pickup.id)"
                      class="text-emerald-600 hover:text-emerald-800"
                    >
                      電子印押下
                    </button>
                  </td>
                </tr>
                <tr v-if="pickups.data.length === 0">
                  <td colspan="4" class="px-6 py-10 text-center text-slate-400">該当する集荷伝票がありません。</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ページネーション -->
          <div class="flex justify-between items-center px-6 py-4 border-t border-slate-200">
            <div class="text-sm text-slate-600">
              {{ pickups.from }} - {{ pickups.to }} / 全 {{ pickups.total }} 件
            </div>
            <div class="flex space-x-2">
              <Link
                v-for="link in pickups.links"
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
