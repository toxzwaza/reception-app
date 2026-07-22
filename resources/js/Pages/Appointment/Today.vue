<template>
  <ReceptionLayout
    title="アポイントありの方"
    :subtitle="`${today} のご予約 — ご自身のお名前をタップしてください`"
  >
    <div class="mx-auto max-w-3xl">
      <!-- 一覧 -->
      <div v-if="appointments.length" class="space-y-3">
        <button
          v-for="(a, index) in appointments"
          :key="a.id"
          type="button"
          @click="openConfirm(a)"
          class="fade-up group flex w-full items-center gap-4 rounded-2xl border border-white/70 bg-white/85 p-4 text-left shadow-md backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white hover:shadow-lg disabled:opacity-60"
          :style="{ animationDelay: `${index * 60}ms` }"
        >
          <!-- 時刻 -->
          <div class="flex w-24 shrink-0 flex-col items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-sky-500 px-2 py-3 text-white shadow-sm">
            <span class="text-2xl font-bold tabular-nums leading-none">{{ a.time || '--:--' }}</span>
            <span class="mt-1 text-[11px] font-medium text-blue-50/90">来訪予定</span>
          </div>

          <!-- 詳細 -->
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <span class="truncate text-lg font-bold text-slate-800">{{ a.company_name || '—' }}</span>
              <span v-if="a.is_checked_in" class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600 ring-1 ring-emerald-100">受付済</span>
            </div>
            <div class="mt-0.5 truncate text-base text-slate-600">{{ a.visitor_name || '—' }} 様</div>
            <div class="mt-0.5 truncate text-sm text-slate-400">ご担当： {{ a.staff_name || '未設定' }}</div>
          </div>

          <!-- 矢印 -->
          <svg class="h-6 w-6 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- 予約なし -->
      <div v-else class="fade-up rounded-2xl border border-white/70 bg-white/70 px-8 py-12 text-center shadow-md backdrop-blur-sm">
        <p class="text-lg font-semibold text-slate-600">本日のご予約が見つかりません</p>
        <p class="mt-2 text-sm text-slate-500">
          お手数ですが、下の「担当部署を呼ぶ」からお呼び出しください。
        </p>
        <Link :href="route('department-call.select')" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700">
          担当部署を呼ぶ
        </Link>
      </div>
    </div>

    <!-- 確認モーダル -->
    <div v-if="selected" class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/40 px-4 backdrop-blur-sm" @click.self="closeConfirm">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-800">こちらのご予約でお間違いないですか？</h3>
        <dl class="mt-4 space-y-2 rounded-xl bg-slate-50 p-4 text-sm">
          <div class="flex justify-between"><dt class="text-slate-500">時刻</dt><dd class="font-semibold text-slate-800">{{ selected.time || '—' }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">会社名</dt><dd class="font-semibold text-slate-800">{{ selected.company_name || '—' }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">お名前</dt><dd class="font-semibold text-slate-800">{{ selected.visitor_name || '—' }} 様</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">ご担当</dt><dd class="font-semibold text-slate-800">{{ selected.staff_name || '未設定' }}</dd></div>
        </dl>
        <p class="mt-3 text-sm text-slate-500">「到着を知らせる」を押すと、ご担当者へ通知します。</p>
        <div class="mt-5 flex justify-end gap-2">
          <button type="button" @click="closeConfirm" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200">戻る</button>
          <button type="button" :disabled="sending" @click="notifyArrival" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50">
            {{ sending ? '通知中…' : '到着を知らせる' }}
          </button>
        </div>
      </div>
    </div>

    <!-- 完了オーバーレイ -->
    <div v-if="done" class="fixed inset-0 z-[95] flex items-center justify-center bg-white/90 px-4 backdrop-blur-sm">
      <div class="fade-up text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
          <svg class="h-11 w-11 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="mt-5 text-2xl font-bold text-slate-800">担当者へお知らせしました</h2>
        <p class="mt-2 text-base text-slate-600">{{ doneStaff }} がまいります。少々お待ちください。</p>
        <p class="mt-6 text-sm text-slate-400">まもなくこの画面は戻ります…</p>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

defineProps({
  appointments: { type: Array, default: () => [] },
  today: { type: String, default: '' },
});

const selected = ref(null);
const sending = ref(false);
const done = ref(false);
const doneStaff = ref('');

const openConfirm = (a) => {
  selected.value = a;
};
const closeConfirm = () => {
  if (!sending.value) selected.value = null;
};

const notifyArrival = () => {
  if (!selected.value) return;
  sending.value = true;
  const staff = selected.value.staff_name || '担当者';
  router.post(route('appointment.today.notify', selected.value.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      selected.value = null;
      doneStaff.value = staff;
      done.value = true;
      // 数秒後に受付トップへ戻る
      setTimeout(() => {
        done.value = false;
        router.visit(route('home'));
      }, 5000);
    },
    onFinish: () => {
      sending.value = false;
    },
  });
};
</script>

<style scoped>
.fade-up {
  opacity: 0;
  animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(14px); }
  to { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
  .fade-up { opacity: 1; animation: none; }
}
</style>
