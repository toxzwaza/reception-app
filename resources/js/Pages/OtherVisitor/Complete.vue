<template>
  <ReceptionLayout 
    title="その他の方" 
    subtitle="受付が完了しました"
    :steps="['訪問者情報入力', '部署選択', '完了']"
    :current-step="2"
  >
    <div class="p-12 text-center">
          <div class="w-24 h-24 mx-auto mb-6 text-indigo-500">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="animate-pulse">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          
          <h2 class="text-3xl font-bold text-gray-900 mb-4">
            担当者へおつなぎします
          </h2>
          
          <p class="text-xl text-gray-600 mb-8">
            もうしばらくお待ちください
          </p>

          <div class="flex justify-center space-x-2 mb-8">
            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
          </div>

          <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
            <p class="text-gray-700">
              担当部署へ訪問者情報を送信しました。<br>
              担当者が確認次第、ご案内いたします。<br>
              受付付近でお待ちください。
            </p>
          </div>

          <div v-if="visitorInfo" class="bg-gray-50 rounded-lg p-6 text-left">
            <h3 class="font-semibold text-gray-900 mb-3">受付情報</h3>
            <div class="space-y-2 text-sm">
              <p><span class="font-medium">社名:</span> {{ visitorInfo.company_name }}</p>
              <p><span class="font-medium">氏名:</span> {{ visitorInfo.visitor_name }}</p>
              <p><span class="font-medium">人数:</span> {{ visitorInfo.number_of_people }}名</p>
              <p><span class="font-medium">要件:</span> {{ visitorInfo.purpose }}</p>
            </div>
          </div>
        </div>
  </ReceptionLayout>
</template>

<script setup>
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

defineProps({
  visitorInfo: {
    type: Object,
    default: null
  }
});
</script>

