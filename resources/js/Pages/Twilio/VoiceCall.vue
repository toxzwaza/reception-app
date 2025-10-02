<template>
  <ReceptionLayout 
    title="Twilioリアルタイム音声通話" 
    subtitle="ブラウザから直接電話をかけることができます"
  >
    <div class="p-8 max-w-4xl mx-auto">
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
      </div>

      <!-- メイン通話UI -->
      <div class="bg-white rounded-xl shadow-lg p-8 border-2 border-gray-200">
        <!-- デバイス状態インジケーター -->
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center">
            <div :class="[
              'w-3 h-3 rounded-full mr-3',
              deviceStatus === 'ready' ? 'bg-green-500' :
              deviceStatus === 'connecting' ? 'bg-yellow-500 animate-pulse' :
              'bg-red-500'
            ]"></div>
            <span class="text-sm font-medium text-gray-700">
              {{ deviceStatusText }}
            </span>
          </div>
          <button
            v-if="deviceStatus !== 'ready' && deviceStatus !== 'connecting'"
            @click="initializeDevice"
            class="text-sm px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
          >
            接続する
          </button>
        </div>

        <!-- 通話中の表示 -->
        <div v-if="isOnCall" class="text-center py-8">
          <div class="mb-6">
            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center animate-pulse">
              <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
            </div>
          </div>
          
          <h2 class="text-2xl font-bold text-gray-900 mb-2">通話中</h2>
          <p class="text-lg text-gray-600 mb-1">{{ currentPhoneNumber }}</p>
          <p class="text-sm text-gray-500">{{ callDuration }}</p>

          <div class="mt-8 flex gap-4 justify-center">
            <!-- ミュートボタン -->
            <button
              @click="toggleMute"
              :class="[
                'w-16 h-16 rounded-full flex items-center justify-center transition-colors',
                isMuted ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-200 hover:bg-gray-300'
              ]"
            >
              <svg v-if="!isMuted" class="w-8 h-8" :class="isMuted ? 'text-white' : 'text-gray-700'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
              </svg>
              <svg v-else class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
              </svg>
            </button>

            <!-- 切断ボタン -->
            <button
              @click="hangup"
              class="w-20 h-20 bg-red-600 hover:bg-red-700 rounded-full flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform"
            >
              <svg class="w-10 h-10 text-white transform rotate-135" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- 待機中の表示 -->
        <div v-else>
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">発信先電話番号</label>
            <input
              v-model="phoneNumber"
              type="tel"
              placeholder="+81901234567"
              :disabled="!isDeviceReady"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg disabled:bg-gray-100"
            />
            <p class="mt-1 text-xs text-gray-500">国際形式で入力してください（例: +81901234567）</p>
          </div>

          <button
            @click="makeCall"
            :disabled="!phoneNumber || !isDeviceReady || isConnecting"
            class="w-full py-4 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center justify-center text-lg"
          >
            <svg v-if="isConnecting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            {{ isConnecting ? '発信中...' : '電話をかける' }}
          </button>
        </div>
      </div>

      <!-- 設定情報 -->
      <div class="mt-8 bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3 flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          リアルタイム音声通話について
        </h3>
        <div class="text-sm text-yellow-800 space-y-2">
          <p>この機能では、ブラウザのマイク・スピーカーを使用して双方向の音声通話が可能です。</p>
          <ul class="list-disc list-inside ml-4 space-y-1">
            <li>初回はマイクへのアクセス許可が必要です</li>
            <li>トライアルアカウントでは検証済み電話番号にのみ発信可能</li>
            <li>通話中はミュート機能が利用できます</li>
          </ul>
          <p class="mt-3">
            <strong>必要な環境変数:</strong><br>
            <code class="bg-yellow-100 px-2 py-1 rounded text-xs">TWILIO_API_KEY</code>、
            <code class="bg-yellow-100 px-2 py-1 rounded text-xs">TWILIO_API_SECRET</code>、
            <code class="bg-yellow-100 px-2 py-1 rounded text-xs">TWILIO_TWIML_APP_SID</code>
          </p>
          <p class="mt-2 text-xs">
            セットアップ方法は <strong>TWILIO_VOICE_SETUP.md</strong> を参照してください。
          </p>
        </div>
      </div>

      <!-- ナビゲーション -->
      <div class="mt-8 flex gap-4">
        <Link href="/" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          ホームに戻る
        </Link>
        <Link href="/twilio-test" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
          SMS/音声メッセージテスト
        </Link>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, markRaw } from 'vue';
import { Link } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import { Device } from '@twilio/voice-sdk';

// 状態管理
// Twilioオブジェクトはリアクティブにしない（markRawを使用）
let device = null;
let currentCall = null;
const phoneNumber = ref('');
const currentPhoneNumber = ref('');
const deviceStatus = ref('disconnected'); // 'disconnected', 'connecting', 'ready', 'error'
const isOnCall = ref(false);
const isConnecting = ref(false);
const isMuted = ref(false);
const statusMessage = ref('');
const statusType = ref('');
const callStartTime = ref(null);
const callDuration = ref('00:00');
let durationInterval = null;

// 算出プロパティ
const deviceStatusText = computed(() => {
  switch (deviceStatus.value) {
    case 'ready': return 'デバイス準備完了';
    case 'connecting': return '接続中...';
    case 'error': return 'エラー: 接続に失敗';
    default: return 'デバイス未接続';
  }
});

const isDeviceReady = computed(() => deviceStatus.value === 'ready');

// デバイスの初期化
const initializeDevice = async () => {
  try {
    deviceStatus.value = 'connecting';
    showStatus('info', 'デバイスを初期化しています...');

    // アクセストークンを取得
    const response = await axios.post(route('twilio-voice.token'));
    const data = response.data;

    if (!data.success) {
      throw new Error(data.message);
    }

    // Twilio Deviceを初期化（markRawでリアクティブシステムから除外）
    device = markRaw(new Device(data.token, {
      logLevel: 1,
      codecPreferences: ['opus', 'pcmu'],
    }));

    // イベントリスナーを設定
    setupDeviceListeners();

    // デバイス登録
    await device.register();

    deviceStatus.value = 'ready';
    showStatus('success', 'デバイスの準備が完了しました');

  } catch (error) {
    console.error('Device initialization error:', error);
    deviceStatus.value = 'error';
    showStatus('error', 'デバイスの初期化に失敗しました: ' + error.message);
  }
};

// デバイスイベントリスナーの設定
const setupDeviceListeners = () => {
  if (!device) return;

  device.on('registered', () => {
    console.log('Device registered');
  });

  device.on('error', (error) => {
    console.error('Device error:', error);
    showStatus('error', 'デバイスエラー: ' + error.message);
  });

  device.on('incoming', (call) => {
    console.log('Incoming call from:', call.parameters.From);
    // 着信処理（必要に応じて実装）
  });
};

// 電話をかける
const makeCall = async () => {
  if (!phoneNumber.value || !device) return;

  try {
    isConnecting.value = true;
    currentPhoneNumber.value = phoneNumber.value;

    // 通話を開始
    const params = {
      To: phoneNumber.value,
    };

    currentCall = markRaw(await device.connect({ params }));

    // 通話イベントリスナーを設定
    setupCallListeners();

  } catch (error) {
    console.error('Call error:', error);
    isConnecting.value = false;
    showStatus('error', '通話の開始に失敗しました: ' + error.message);
  }
};

// 通話イベントリスナーの設定
const setupCallListeners = () => {
  if (!currentCall) return;

  currentCall.on('accept', () => {
    console.log('Call accepted');
    isConnecting.value = false;
    isOnCall.value = true;
    callStartTime.value = Date.now();
    startDurationTimer();
    showStatus('success', '通話を開始しました');
  });

  currentCall.on('disconnect', () => {
    console.log('Call disconnected');
    endCall();
  });

  currentCall.on('cancel', () => {
    console.log('Call cancelled');
    endCall();
  });

  currentCall.on('reject', () => {
    console.log('Call rejected');
    endCall();
  });

  currentCall.on('error', (error) => {
    console.error('Call error:', error);
    showStatus('error', '通話エラー: ' + error.message);
    endCall();
  });
};

// 通話を切断
const hangup = () => {
  if (currentCall) {
    currentCall.disconnect();
  }
};

// 通話終了処理
const endCall = () => {
  isOnCall.value = false;
  isConnecting.value = false;
  isMuted.value = false;
  currentCall = null;
  currentPhoneNumber.value = '';
  stopDurationTimer();
  showStatus('info', '通話を終了しました');
};

// ミュート切り替え
const toggleMute = () => {
  if (!currentCall) return;

  isMuted.value = !isMuted.value;
  currentCall.mute(isMuted.value);
  
  showStatus('info', isMuted.value ? 'ミュートしました' : 'ミュートを解除しました');
};

// 通話時間タイマー
const startDurationTimer = () => {
  durationInterval = setInterval(() => {
    if (callStartTime.value) {
      const elapsed = Math.floor((Date.now() - callStartTime.value) / 1000);
      const minutes = Math.floor(elapsed / 60);
      const seconds = elapsed % 60;
      callDuration.value = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
  }, 1000);
};

const stopDurationTimer = () => {
  if (durationInterval) {
    clearInterval(durationInterval);
    durationInterval = null;
  }
  callDuration.value = '00:00';
  callStartTime.value = null;
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

// ライフサイクルフック
onMounted(() => {
  initializeDevice();
});

onUnmounted(() => {
  // クリーンアップ
  if (currentCall) {
    currentCall.disconnect();
  }
  if (device) {
    device.destroy();
  }
  stopDurationTimer();
});
</script>

<style scoped>
/* アニメーション */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

code {
  font-family: 'Courier New', monospace;
}
</style>


