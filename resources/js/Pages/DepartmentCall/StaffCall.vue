<template>
  <ReceptionLayout
    title="担当を呼ぶ"
    subtitle="担当者へおつなぎしています"
  >
    <div class="mx-auto max-w-2xl">
      <!-- 見出し -->
      <div class="mb-6 text-center">
        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600">
          <svg v-if="staffInfo?.call_type === '携帯'" class="h-10 w-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <svg v-else class="h-10 w-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">{{ staffInfo?.name }}さんへおつなぎします</h1>
        <p class="mt-1 text-slate-500">
          <span v-if="staffInfo?.group_name">{{ staffInfo.group_name }}・</span>{{ staffInfo?.call_type }}へ発信します。もうしばらくお待ちください
        </p>
      </div>

      <!-- 担当者への自動発信 -->
      <div v-if="staffInfo?.phone_number" class="rounded-2xl border border-slate-200 bg-white/85 p-6 shadow-sm backdrop-blur-sm">
        <TwilioAutoCall
          :phone-number="staffInfo.phone_number"
          :contact-name="staffInfo.name"
          :department-name="staffInfo.group_name ?? ''"
          :message="`受付から${staffInfo.name}さんをお呼びしています。`"
          :auto-call-delay="1500"
          @call-completed="backToTop"
          @call-failed="backToTop"
        />
      </div>

      <!-- 発信先未登録（通常はサーバー側で弾かれるため保険） -->
      <div v-else class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
        <p class="font-semibold text-amber-800">発信先の電話番号が登録されていません。</p>
        <p class="mt-1 text-sm text-amber-700">管理者に呼出情報の登録を依頼してください。</p>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import TwilioAutoCall from '@/Components/TwilioAutoCall.vue';

defineProps({
  staffInfo: { type: Object, default: null },
});

// 通話終了・失敗後、少し待って受付トップへ戻す
const backToTop = () => {
  setTimeout(() => {
    router.visit(route('home'), { method: 'get', preserveState: false, preserveScroll: false });
  }, 3000);
};
</script>
