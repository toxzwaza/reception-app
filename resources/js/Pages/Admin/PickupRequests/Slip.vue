<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          集荷依頼伝票
        </h2>
        <div class="flex gap-2 print:hidden">
          <Link :href="route('admin.pickup-requests.index')" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition">
            ← 一覧へ
          </Link>
          <button @click="printSlip" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
            🖨 印刷
          </button>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 伝票本体 -->
        <div id="slip" class="bg-white rounded-2xl border border-slate-300 shadow-sm p-8 print:border-0 print:shadow-none">
          <div class="text-center border-b-2 border-slate-800 pb-4 mb-6">
            <h1 class="text-2xl font-bold tracking-wide text-slate-900">集荷依頼伝票</h1>
            <p class="mt-1 text-sm text-slate-500">株式会社アキオカ</p>
          </div>

          <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-200">
              <tr>
                <th class="w-40 bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600">依頼番号</th>
                <td class="px-4 py-3 text-slate-900">No. {{ String(pickupRequest.id).padStart(5, '0') }}</td>
              </tr>
              <tr>
                <th class="bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600">依頼者</th>
                <td class="px-4 py-3 text-slate-900">{{ pickupRequest.requester_name }}</td>
              </tr>
              <tr>
                <th class="bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600">物品</th>
                <td class="px-4 py-3 text-slate-900">{{ pickupRequest.item }}</td>
              </tr>
              <tr>
                <th class="bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600">置き場所</th>
                <td class="px-4 py-3 text-slate-900">{{ pickupRequest.storage_location || '—' }}</td>
              </tr>
              <tr>
                <th class="bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600">問い合わせ電話番号</th>
                <td class="px-4 py-3 text-slate-900">{{ pickupRequest.contact_phone || '—' }}</td>
              </tr>
              <tr v-if="pickupRequest.memo">
                <th class="bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600">備考</th>
                <td class="px-4 py-3 whitespace-pre-wrap text-slate-900">{{ pickupRequest.memo }}</td>
              </tr>
              <tr>
                <th class="bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600">登録日時</th>
                <td class="px-4 py-3 text-slate-900">{{ formatDate(pickupRequest.created_at) }}</td>
              </tr>
            </tbody>
          </table>

          <div class="mt-10 flex justify-end gap-10">
            <div class="text-center">
              <div class="mb-1 text-xs text-slate-500">集荷担当者サイン</div>
              <div class="h-16 w-40 border-b border-slate-400"></div>
            </div>
            <div class="text-center">
              <div class="mb-1 text-xs text-slate-500">集荷日時</div>
              <div class="h-16 w-40 border-b border-slate-400"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  pickupRequest: { type: Object, required: true },
});

const printSlip = () => window.print();

const formatDate = (v) =>
  v ? new Intl.DateTimeFormat('ja-JP', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(v)) : '';
</script>

<style>
@media print {
  /* 伝票以外を隠して伝票のみ印刷 */
  body * { visibility: hidden; }
  #slip, #slip * { visibility: visible; }
  #slip { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>
