<template>
  <ReceptionLayout title="面接の方" subtitle="担当者へ連絡中です">
    <div class="p-12 text-center">
      <div class="w-24 h-24 mx-auto mb-6 text-green-500">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="animate-pulse">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      
      <h2 class="text-3xl font-bold text-gray-900 mb-4">
        担当者に連絡します
      </h2>
      
      <p class="text-xl text-gray-600 mb-8">
        もうしばらくお待ちください
      </p>

      <div class="flex justify-center space-x-2 mb-8">
        <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
        <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
      </div>

      <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <p class="text-gray-700">
          担当者が確認次第、ご案内いたします。<br>
          受付付近でお待ちください。
        </p>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

// 面接担当者への通知を送信
onMounted(() => {
  // TODO: 面接担当者へのTeams通知を送信
  console.log('面接担当者へ通知を送信しました');
});
</script>

