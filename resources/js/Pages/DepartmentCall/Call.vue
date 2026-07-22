<template>
  <ReceptionLayout
    title="担当部署を呼ぶ"
    subtitle="担当部署へおつなぎしています"
  >
    <div class="mx-auto max-w-2xl">
      <!-- 見出し -->
      <div class="mb-6 text-center">
        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-600">
          <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">{{ groupInfo?.name }}へおつなぎします</h1>
        <p class="mt-1 text-slate-500">もうしばらくお待ちください</p>
      </div>

      <!-- 部署への自動発信 -->
      <div v-if="groupInfo?.phone_number" class="rounded-2xl border border-slate-200 bg-white/85 p-6 shadow-sm backdrop-blur-sm">
        <TwilioAutoCall
          :phone-number="groupInfo.phone_number"
          :contact-name="groupInfo.name"
          :department-name="groupInfo.name"
          :message="`受付から${groupInfo.name}をお呼びしています。`"
          :auto-call-delay="1500"
          @call-completed="backToTop"
          @call-failed="backToTop"
        />
      </div>

      <!-- 電話番号未登録 -->
      <div v-else class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
        <p class="font-semibold text-amber-800">{{ groupInfo?.name }}の電話番号が登録されていません。</p>
        <p class="mt-1 text-sm text-amber-700">管理者に部署の電話番号の登録を依頼してください。</p>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import TwilioAutoCall from '@/Components/TwilioAutoCall.vue';

defineProps({
  groupInfo: { type: Object, default: null },
});

// 通話終了・失敗後、少し待って受付トップへ戻す
const backToTop = () => {
  setTimeout(() => {
    router.visit(route('home'), { method: 'get', preserveState: false, preserveScroll: false });
  }, 3000);
};
</script>
