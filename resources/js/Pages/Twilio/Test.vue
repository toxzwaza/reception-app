<template>
  <ReceptionLayout 
    title="Twilio電話機能テスト" 
    subtitle="電話発信・SMS送信のテストを行います"
  >
    <div class="p-8 max-w-6xl mx-auto">
      <!-- ステータス表示エリア -->
      <div v-if="statusMessage" 
           :class="[
             'mb-6 p-4 rounded-lg',
             statusType === 'success' ? 'bg-green-100 border-2 border-green-500 text-green-800' : 
             statusType === 'error' ? 'bg-red-100 border-2 border-red-500 text-red-800' :
             'bg-blue-100 border-2 border-blue-500 text-blue-800'
           ]">
        <div class="flex items-center">
          <svg v-if="statusType === 'success'" class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <svg v-else-if="statusType === 'error'" class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span class="font-semibold">{{ statusMessage }}</span>
        </div>
        <div v-if="callData" class="mt-2 text-sm">
          <p>Call SID: {{ callData.call_sid }}</p>
          <p>Status: {{ callData.status }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 電話発信セクション -->
        <div class="bg-white rounded-xl shadow-lg p-8 border-2 border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">電話発信テスト</h2>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">発信先電話番号</label>
              <input
                v-model="callForm.to_number"
                type="tel"
                placeholder="+81901234567"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
              <p class="mt-1 text-xs text-gray-500">国際形式で入力してください（例: +81901234567）</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">発信元電話番号（オプション）</label>
              <input
                v-model="callForm.from_number"
                type="tel"
                placeholder="環境変数の値を使用"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">音声メッセージ</label>
              <textarea
                v-model="callForm.message"
                rows="3"
                placeholder="これはテストコールです。"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              ></textarea>
            </div>

            <button
              @click="makeCall"
              :disabled="!callForm.to_number || isLoading"
              class="w-full py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
            >
              <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isLoading ? '発信中...' : '電話を発信' }}
            </button>
          </div>

          <!-- Call Status Check -->
          <div v-if="callData && callData.call_sid" class="mt-6 pt-6 border-t-2 border-gray-200">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Call SID</label>
            <div class="flex gap-2">
              <input
                :value="callData.call_sid"
                readonly
                class="flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg bg-gray-50 text-sm"
              />
              <button
                @click="checkCallStatus"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-colors"
              >
                ステータス確認
              </button>
            </div>
          </div>
        </div>

        <!-- SMS送信セクション -->
        <div class="bg-white rounded-xl shadow-lg p-8 border-2 border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">SMS送信テスト</h2>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">送信先電話番号</label>
              <input
                v-model="smsForm.to_number"
                type="tel"
                placeholder="+81901234567"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              />
              <p class="mt-1 text-xs text-gray-500">国際形式で入力してください（例: +81901234567）</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">メッセージ</label>
              <textarea
                v-model="smsForm.message"
                rows="5"
                placeholder="送信するメッセージを入力してください"
                maxlength="1600"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500 text-right">{{ smsForm.message.length }}/1600文字</p>
            </div>

            <button
              @click="sendSms"
              :disabled="!smsForm.to_number || !smsForm.message || isSmsLoading"
              class="w-full py-4 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
            >
              <svg v-if="isSmsLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isSmsLoading ? '送信中...' : 'SMSを送信' }}
            </button>
          </div>
        </div>
      </div>

      <!-- 設定情報 -->
      <div class="mt-8 bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3 flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          環境変数設定について
        </h3>
        <div class="text-sm text-yellow-800 space-y-2">
          <p>このテストを実行するには、以下の環境変数が必要です：</p>
          <ul class="list-disc list-inside ml-4 space-y-1">
            <li><code class="bg-yellow-100 px-2 py-1 rounded">TWILIO_ACCOUNT_SID</code> - TwilioアカウントSID</li>
            <li><code class="bg-yellow-100 px-2 py-1 rounded">TWILIO_AUTH_TOKEN</code> - Twilio認証トークン</li>
            <li><code class="bg-yellow-100 px-2 py-1 rounded">TWILIO_PHONE_NUMBER</code> - Twilioの電話番号</li>
          </ul>
          <p class="mt-3">
            <strong>注意：</strong>日本の電話番号へSMSを送信するには、Twilioで日本の電話番号を取得する必要があります。
          </p>
        </div>
      </div>

      <!-- ホームに戻るボタン -->
      <div class="mt-8">
        <Link href="/" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          ホームに戻る
        </Link>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

// フォームデータ
const callForm = reactive({
  to_number: '',
  from_number: '',
  message: 'これはテストコールです。',
});

const smsForm = reactive({
  to_number: '',
  message: '',
});

// 状態管理
const isLoading = ref(false);
const isSmsLoading = ref(false);
const statusMessage = ref('');
const statusType = ref(''); // 'success', 'error', 'info'
const callData = ref(null);

// 電話発信
const makeCall = async () => {
  if (!callForm.to_number) {
    showStatus('error', '発信先電話番号を入力してください');
    return;
  }

  isLoading.value = true;
  statusMessage.value = '';

  try {
    const response = await axios.post(route('twilio-test.make-call'), callForm);
    const data = response.data;

    if (data.success) {
      callData.value = data.data;
      showStatus('success', data.message);
    } else {
      showStatus('error', data.message);
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message;
    showStatus('error', 'エラーが発生しました: ' + errorMessage);
  } finally {
    isLoading.value = false;
  }
};

// Call Statusの確認
const checkCallStatus = async () => {
  if (!callData.value || !callData.value.call_sid) {
    showStatus('error', 'Call SIDが見つかりません');
    return;
  }

  try {
    const response = await axios.post(route('twilio-test.check-status'), {
      call_sid: callData.value.call_sid
    });
    const data = response.data;

    if (data.success) {
      callData.value = data.data;
      showStatus('info', `ステータス: ${data.data.status} | 通話時間: ${data.data.duration || 0}秒`);
    } else {
      showStatus('error', data.message);
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message;
    showStatus('error', 'エラーが発生しました: ' + errorMessage);
  }
};

// SMS送信
const sendSms = async () => {
  if (!smsForm.to_number || !smsForm.message) {
    showStatus('error', '電話番号とメッセージを入力してください');
    return;
  }

  isSmsLoading.value = true;
  statusMessage.value = '';

  try {
    const response = await axios.post(route('twilio-test.send-sms'), smsForm);
    const data = response.data;

    if (data.success) {
      showStatus('success', data.message);
      // フォームをクリア
      smsForm.message = '';
    } else {
      showStatus('error', data.message);
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message;
    showStatus('error', 'エラーが発生しました: ' + errorMessage);
  } finally {
    isSmsLoading.value = false;
  }
};

// ステータスメッセージ表示
const showStatus = (type, message) => {
  statusType.value = type;
  statusMessage.value = message;
  
  // 5秒後に自動で消す（エラー以外）
  if (type !== 'error') {
    setTimeout(() => {
      statusMessage.value = '';
    }, 5000);
  }
};
</script>

<style scoped>
code {
  font-family: 'Courier New', monospace;
}
</style>

