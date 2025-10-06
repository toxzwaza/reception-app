<template>
  <ReceptionLayout title="面接の方" subtitle="担当者へ連絡中です">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
      <div class="max-w-2xl w-full">
        <!-- ヘッダー -->
        <div class="text-center mb-8">
          <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          
          <h1 class="text-2xl font-bold text-gray-900 mb-2">
            担当者に連絡します
          </h1>
          
          <p class="text-gray-600">
            もうしばらくお待ちください
          </p>
        </div>

        <!-- 面接担当者への自動通話 -->
        <div v-if="interviewPhones && interviewPhones.length > 0" class="space-y-4">
          <TwilioAutoCall
            v-for="(phone, index) in interviewPhones"
            :key="phone.id"
            :phone-number="phone.phone_number"
            :contact-name="phone.contact_person"
            :department-name="phone.department_name"
            :message="`面接の方が到着しました。${phone.department_name}の${phone.contact_person}さんをお呼びください。`"
            :auto-call-delay="index * 3000"
            @call-started="onCallStarted"
            @call-completed="onCallCompleted"
            @call-failed="onCallFailed"
            @call-cancelled="onCallCancelled"
          />
        </div>

        <!-- 面接用電話番号が登録されていない場合 -->
        <div v-else class="bg-white rounded-xl shadow-lg p-8 text-center">
          <div class="w-16 h-16 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            面接用電話番号が登録されていません
          </h3>
          
          <p class="text-gray-600">
            管理者に面接用電話番号の登録を依頼してください。
          </p>
        </div>

        <!-- 案内メッセージ -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6 text-center">
          <p class="text-gray-700">
            担当者が確認次第、ご案内いたします。<br>
            受付付近でお待ちください。
          </p>
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

// Props
const props = defineProps({
  interviewPhones: {
    type: Array,
    default: () => []
  }
});

// 面接担当者への通知を送信
onMounted(() => {
  console.log('面接担当者へ通知を送信しました');
  console.log('interviewPhones:', props.interviewPhones);
  console.log('interviewPhones length:', props.interviewPhones?.length);
});

// 通話イベントハンドラー
const onCallStarted = (callData) => {
  console.log('通話開始:', callData);
};

const onCallCompleted = (callData) => {
  console.log('通話完了:', callData);
  
  // 通話完了後、3秒後にTOP画面にリダイレクト
  setTimeout(() => {
    router.visit('/', {
      method: 'get',
      preserveState: false,
      preserveScroll: false
    });
  }, 3000);
};

const onCallFailed = (error) => {
  console.log('通話失敗:', error);
  
  // 通話失敗後、5秒後にTOP画面にリダイレクト
  setTimeout(() => {
    router.visit('/', {
      method: 'get',
      preserveState: false,
      preserveScroll: false
    });
  }, 5000);
};

const onCallCancelled = () => {
  console.log('発信キャンセル');
};
</script>

