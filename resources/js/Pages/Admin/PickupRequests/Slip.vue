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
      <div class="mx-auto px-4 sm:px-6 lg:px-8" style="max-width: 160mm;">
        <p class="mb-3 text-center text-sm text-slate-500 print:hidden">
          A4用紙（横）に印刷され、中央の切り取り線で切り取るとA5縦サイズの伝票になります。
        </p>

        <!-- A4横の左半分（A5縦）を想定した伝票本体 -->
        <div
          id="slip"
          class="mx-auto bg-white text-slate-900"
          style="width: 148.5mm; min-height: 210mm; padding: 12mm; box-sizing: border-box; border: 1px solid #cbd5e1;"
        >
          <!-- ヘッダー -->
          <div class="text-center" style="border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 18px;">
            <h1 style="font-size: 20pt; font-weight: 700; letter-spacing: 0.05em;">集荷依頼伝票</h1>
            <p style="margin-top: 4px; font-size: 9pt; color: #64748b;">株式会社アキオカ</p>
          </div>

          <table style="width: 100%; font-size: 10.5pt; border-collapse: collapse;">
            <tbody>
              <tr v-for="row in rows" :key="row.label" style="border-bottom: 1px solid #e2e8f0;">
                <th style="width: 34mm; background: #f8fafc; padding: 8px 10px; text-align: left; font-weight: 600; color: #475569; vertical-align: top;">
                  {{ row.label }}
                </th>
                <td style="padding: 8px 10px; white-space: pre-wrap;">{{ row.value }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 切り取り線（印刷時、A4横の中央=148.5mmに縦の点線） -->
    <div id="cutline" aria-hidden="true"></div>
    <div id="cutlabel" aria-hidden="true">✂ 切り取り線</div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  pickupRequest: { type: Object, required: true },
  departmentName: { type: String, default: null },
});

const printSlip = () => window.print();

const formatDate = (v) =>
  v ? new Intl.DateTimeFormat('ja-JP', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(v)) : '';

const rows = computed(() => {
  const r = props.pickupRequest;
  const list = [
    { label: '依頼番号', value: 'No. ' + String(r.id).padStart(5, '0') },
    { label: '依頼者', value: r.requester_name },
    { label: '所属部署', value: props.departmentName || '—' },
    { label: '物品', value: r.item },
    { label: '置き場所', value: r.storage_location || '—' },
    { label: '問い合わせ電話番号', value: r.contact_phone || '—' },
  ];
  if (r.memo) list.push({ label: '備考', value: r.memo });
  list.push({ label: '登録日時', value: formatDate(r.created_at) });
  return list;
});
</script>

<style>
/* 画面上では切り取り線は非表示 */
#cutline,
#cutlabel {
  display: none;
}

@media print {
  @page {
    size: A4 landscape; /* A4横 297mm × 210mm */
    margin: 0;
  }

  /* 伝票以外を隠す */
  body * {
    visibility: hidden;
  }
  #slip,
  #slip * {
    visibility: visible;
  }

  /* 伝票をA4横の左半分（148.5mm × 210mm = A5縦）に配置 */
  #slip {
    position: fixed;
    left: 0;
    top: 0;
    width: 148.5mm;
    height: 210mm;
    margin: 0;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
  }

  /* 中央（148.5mm）に縦の切り取り線 */
  #cutline {
    display: block;
    visibility: visible;
    position: fixed;
    top: 0;
    left: 148.5mm;
    width: 0;
    height: 210mm;
    border-left: 1.5px dashed #9aa4b2;
  }
  #cutlabel {
    display: block;
    visibility: visible;
    position: fixed;
    left: 150mm;
    top: 95mm;
    font-size: 8pt;
    color: #9aa4b2;
    white-space: nowrap;
    transform: rotate(90deg);
    transform-origin: left top;
  }
}
</style>
