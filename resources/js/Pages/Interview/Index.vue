<template>
  <ReceptionLayout title="面接の方" :subtitle="subtitle">
    <div class="p-12">
      <div class="max-w-4xl mx-auto">

        <!-- ① 初期状態: 呼び出しボタン表示（通知・発信は未発火） -->
        <div v-if="!started" class="bg-white rounded-xl border-2 border-indigo-200 shadow-lg p-10 text-center">
          <div class="w-24 h-24 mx-auto mb-6 bg-indigo-100 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>

          <h2 class="text-2xl font-bold text-gray-900 mb-3">
            面接にお越しの方
          </h2>
          <p class="text-gray-600 mb-8 text-lg">
            下のボタンを押すと担当者に連絡します
          </p>

          <button
            type="button"
            @click="startNotification"
            :disabled="isNotifying || !hasPhones"
            class="w-full max-w-md mx-auto py-6 bg-indigo-600 text-white text-2xl font-bold rounded-xl shadow-lg hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="isNotifying">📣 呼び出し中...</span>
            <span v-else-if="!hasPhones">📵 担当者情報が登録されていません</span>
            <span v-else>📣 担当者を呼ぶ</span>
          </button>

          <!-- エラーメッセージ -->
          <p v-if="errorMessage" class="mt-6 text-red-600 text-sm">{{ errorMessage }}</p>

          <!-- 電話番号未登録時の注意書き -->
          <p v-if="!hasPhones" class="mt-6 text-yellow-700 text-sm">
            管理者に面接用電話番号の登録を依頼してください。
          </p>
        </div>

        <!-- ② 呼び出し後: Teams通知送信済み + TwilioAutoCall 描画（電話発信開始） -->
        <div v-else class="space-y-6">
          <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
            <p class="text-green-800 font-semibold">
              ✅ 担当者に連絡しました。お待ちください。
            </p>
          </div>

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

      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import TwilioAutoCall from '@/Components/TwilioAutoCall.vue';

const props = defineProps({
  interviewPhones: {
    type: Array,
    default: () => [],
  },
});

// 状態
const started = ref(false);        // true = 通知+発信を開始済み
const isNotifying = ref(false);    // true = POST 中（多重押下防止）
const errorMessage = ref('');

const hasPhones = computed(() => Array.isArray(props.interviewPhones) && props.interviewPhones.length > 0);
const subtitle = computed(() => started.value ? '担当者へ連絡中です' : 'ボタンを押して呼び出してください');

// 「担当者を呼ぶ」ボタン押下時の処理
const startNotification = async () => {
  if (isNotifying.value || started.value) return;
  if (!hasPhones.value) {
    errorMessage.value = '担当者情報が登録されていません';
    return;
  }

  isNotifying.value = true;
  errorMessage.value = '';

  try {
    // Teams 通知を送信（明示的ボタン押下でのみ発火）
    await axios.post(route('interview.notify-staff'));
    console.log('✅ Teams 通知送信');

    // TwilioAutoCall コンポーネントをレンダリングして発信開始
    started.value = true;
  } catch (error) {
    console.error('Teams 通知エラー:', error);
    errorMessage.value = 'Teams 通知の送信に失敗しました。担当者に直接お伝えください。';
    // Teams 通知が失敗しても電話発信だけは進めるかどうかは運用判断
    // ここでは電話発信も止める（UX: 通知できていない旨を表示）
  } finally {
    isNotifying.value = false;
  }
};

// 通話イベントハンドラー
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
