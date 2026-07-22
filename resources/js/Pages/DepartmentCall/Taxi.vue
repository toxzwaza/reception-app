<template>
  <ReceptionLayout
    title="タクシーを呼ぶ"
    subtitle="タクシー会社へおつなぎしています"
  >
    <div class="mx-auto max-w-2xl">
      <!-- 見出し -->
      <div class="mb-6 text-center">
        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600">
          <span class="text-3xl">🚕</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">タクシー会社へおつなぎします</h1>
        <p class="mt-1 text-slate-500">もうしばらくお待ちください</p>
      </div>

      <!-- タクシー会社への自動発信 -->
      <div v-if="phoneNumber" class="rounded-2xl border border-slate-200 bg-white/85 p-6 shadow-sm backdrop-blur-sm">
        <TwilioAutoCall
          :phone-number="phoneNumber"
          contact-name="タクシー会社"
          department-name="タクシー会社"
          message="受付からタクシーの配車をお願いします。"
          :auto-call-delay="1500"
          @call-completed="backToTop"
          @call-failed="backToTop"
        />
      </div>

      <!-- 電話番号未登録 -->
      <div v-else class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
        <p class="font-semibold text-amber-800">タクシー会社の電話番号が登録されていません。</p>
        <p class="mt-1 text-sm text-amber-700">管理画面の「通知設定管理」でタクシー会社の電話番号を登録してください。</p>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import TwilioAutoCall from '@/Components/TwilioAutoCall.vue';

defineProps({
  phoneNumber: { type: String, default: null },
});

// 通話終了・失敗後、少し待って受付トップへ戻す
const backToTop = () => {
  setTimeout(() => {
    router.visit(route('home'), { method: 'get', preserveState: false, preserveScroll: false });
  }, 3000);
};
</script>
