<template>
  <ReceptionLayout 
    title="その他の方" 
    subtitle="受付が完了しました"
    :steps="['訪問者情報入力', '部署選択', '完了']"
    :current-step="2"
  >
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100 flex items-center justify-center p-4">
      <div class="max-w-2xl w-full">
        <!-- ヘッダー -->
        <div class="text-center mb-8">
          <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          
          <h1 class="text-2xl font-bold text-gray-900 mb-2">
            担当者へおつなぎします
          </h1>
          
          <p class="text-gray-600">
            もうしばらくお待ちください
          </p>
        </div>

        <!-- 部署への自動通話 -->
        <div v-if="groupInfo && groupInfo.phone_number" class="mb-8">
          <TwilioAutoCall
            :phone-number="groupInfo.phone_number"
            :contact-name="groupInfo.name"
            :department-name="groupInfo.name"
            :message="`${visitorInfo.visitor_name}様から${visitorInfo.company_name}が訪問に来られました。${visitorInfo.purpose}の件でお話をしたいとのことです。`"
            :auto-call-delay="2000"
            @call-started="onCallStarted"
            @call-completed="onCallCompleted"
            @call-failed="onCallFailed"
            @call-cancelled="onCallCancelled"
          />
        </div>

        <!-- 部署の電話番号が登録されていない場合 -->
        <div v-else-if="groupInfo" class="mb-8 bg-white rounded-xl shadow-lg p-8 text-center">
          <div class="w-16 h-16 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            {{ groupInfo.name }}の電話番号が登録されていません
          </h3>
          
          <p class="text-gray-600">
            管理者に部署の電話番号の登録を依頼してください。
          </p>
        </div>

        <!-- 案内メッセージ -->
        <div class="bg-white rounded-xl shadow-lg p-6 text-center mb-6">
          <p class="text-gray-700">
            担当部署へ訪問者情報を送信しました。<br>
            担当者が確認次第、ご案内いたします。<br>
            受付付近でお待ちください。
          </p>
        </div>

        <!-- 受付情報 -->
        <div v-if="visitorInfo" class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-4 text-center">受付情報</h3>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between">
              <span class="font-medium text-gray-600">社名:</span>
              <span class="text-gray-900">{{ visitorInfo.company_name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="font-medium text-gray-600">氏名:</span>
              <span class="text-gray-900">{{ visitorInfo.visitor_name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="font-medium text-gray-600">人数:</span>
              <span class="text-gray-900">{{ visitorInfo.number_of_people }}名</span>
            </div>
            <div class="flex justify-between">
              <span class="font-medium text-gray-600">要件:</span>
              <span class="text-gray-900">{{ visitorInfo.purpose }}</span>
            </div>
            <div v-if="groupInfo" class="flex justify-between">
              <span class="font-medium text-gray-600">訪問先部署:</span>
              <span class="text-gray-900">{{ groupInfo.name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import TwilioAutoCall from '@/Components/TwilioAutoCall.vue';

const props = defineProps({
  visitorInfo: {
    type: Object,
    default: null
  },
  groupInfo: {
    type: Object,
    default: null
  }
});

onMounted(() => {
  console.log('部署への通知と通話を開始しました');
});

const onCallStarted = (callData) => {
  console.log('通話開始:', callData);
};

const onCallCompleted = (callData) => {
  console.log('通話完了:', callData);
  setTimeout(() => {
    router.visit('/', { method: 'get', preserveState: false, preserveScroll: false });
  }, 3000);
};

const onCallFailed = (error) => {
  console.log('通話失敗:', error);
  setTimeout(() => {
    router.visit('/', { method: 'get', preserveState: false, preserveScroll: false });
  }, 5000);
};

const onCallCancelled = () => {
  console.log('発信キャンセル');
};
</script>