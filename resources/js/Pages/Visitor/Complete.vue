<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-8 text-center">
            <div class="mb-6">
              <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>

            <h1 class="text-2xl font-bold mb-6 text-gray-900">受付が完了しました</h1>

            <!-- 固定表示：時間・施設名・担当者 -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
              <dl class="space-y-3">
                <div class="flex">
                  <dt class="w-24 flex-shrink-0 text-sm text-gray-500">時間</dt>
                  <dd class="flex-1 text-base font-medium text-gray-900">{{ visitTime || '—' }}</dd>
                </div>
                <div class="flex">
                  <dt class="w-24 flex-shrink-0 text-sm text-gray-500">施設名</dt>
                  <dd class="flex-1 text-base font-medium text-gray-900">{{ facilityName || '—' }}</dd>
                </div>
                <div class="flex">
                  <dt class="w-24 flex-shrink-0 text-sm text-gray-500">担当者</dt>
                  <dd class="flex-1 text-base font-medium text-gray-900">{{ staffName || '—' }}</dd>
                </div>
              </dl>
            </div>

            <!-- お客様宛てメッセージ（未入力時はデフォルト文言） -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-6 mb-8 text-left">
              <p class="text-lg text-gray-900 whitespace-pre-wrap leading-relaxed">{{ message }}</p>
            </div>

            <Link
              :href="route('home')"
              class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              トップに戻る
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  staffName: {
    type: String,
    default: null,
  },
  facilityName: {
    type: String,
    default: null,
  },
  visitTime: {
    type: String,
    default: null,
  },
  message: {
    type: String,
    default: 'ただいま担当者をお呼びしております。もうしばらくお待ちください。',
  },
});
</script>
