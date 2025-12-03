<template>
  <div class="twilio-auto-call-container">
    <!-- 通話中の表示 -->
    <div v-if="isOnCall" class="call-in-progress">
      <div class="call-animation">
        <div class="w-32 h-32 mx-auto bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center animate-pulse">
          <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
        </div>
      </div>
      
      <h2 class="text-2xl font-bold text-gray-900 mb-2">通話中</h2>
      <p class="text-lg text-gray-600 mb-1">{{ contactName }}</p>
      <p class="text-sm text-gray-500">{{ formatDisplayPhone(phoneNumber) }}</p>
      <p class="text-sm text-gray-500">{{ callDuration }}</p>

      <div class="mt-8 flex gap-4 justify-center">
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

    <!-- 発信中の表示 -->
    <div v-else-if="isCalling" class="call-connecting">
      <div class="w-24 h-24 mx-auto mb-6 text-green-500">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="animate-pulse">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
        </svg>
      </div>
      
      <h2 class="text-2xl font-bold text-gray-900 mb-4">
        {{ contactName }}に発信中...
      </h2>
      
      <p class="text-lg text-gray-600 mb-4">
        {{ formatDisplayPhone(phoneNumber) }}
      </p>

      <div class="flex justify-center space-x-2 mb-8">
        <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
        <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
      </div>

      <button
        @click="cancelCall"
        class="px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors"
      >
        発信をキャンセル
      </button>
    </div>

    <!-- カウントダウン中の表示 -->
    <div v-else-if="isCountingDown" class="countdown-display">
      <div class="countdown-circle">
        <div class="countdown-number">{{ countdown }}</div>
      </div>
      
      <h2 class="text-2xl font-bold text-gray-900 mb-4">
        TOP画面に戻ります
      </h2>
      
      <p class="text-gray-600">
        {{ countdown }}秒後に自動的に切り替わります
      </p>
    </div>

    <!-- 待機中の表示 -->
    <div v-else class="call-waiting">
      <div class="waiting-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
        </svg>
      </div>
      
      <h2 class="text-xl font-semibold text-gray-900 mb-2">
        準備中
      </h2>
      
      <p class="text-sm text-gray-500">
        {{ formatDisplayPhone(phoneNumber) }}
      </p>

      <div class="loading-dots">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
      </div>
    </div>

    <!-- ステータスメッセージ -->
    <div v-if="statusMessage" :class="['status-message', statusType]">
      <div class="status-content">
        <svg v-if="statusType === 'success'" class="status-icon" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <svg v-else-if="statusType === 'error'" class="status-icon" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="status-text">{{ statusMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, markRaw } from 'vue';
import { Device } from '@twilio/voice-sdk';
import axios from 'axios';

// Props
const props = defineProps({
  phoneNumber: {
    type: String,
    required: true
  },
  contactName: {
    type: String,
    default: ''
  },
  departmentName: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: '面接の方が到着しました。'
  },
  autoCallDelay: {
    type: Number,
    default: 2000 // 2秒後に自動発信
  }
});

// Emits
const emit = defineEmits(['call-started', 'call-completed', 'call-failed', 'call-cancelled']);

// Twilio Device SDK用の状態
let device = null;
let currentCall = null;
const deviceStatus = ref('disconnected'); // 'disconnected', 'connecting', 'ready', 'error'
const isConnecting = ref(false);

// 通話状態
const isCalling = ref(false);
const isOnCall = ref(false);
const isMuted = ref(false);
const statusMessage = ref('');
const statusType = ref('');
const callData = ref(null);
const callStartTime = ref(null);
const callDuration = ref('00:00');
let durationInterval = null;
let callTimeout = null;

// カウントダウン状態
const isCountingDown = ref(false);
const countdown = ref(3);
let countdownInterval = null;

// 電話番号のフォーマット変換（090→+81）
const formatPhoneNumber = (phone) => {
  if (!phone) return '';
  
  // 既に +81 で始まっている場合はそのまま返す
  if (phone.startsWith('+81')) {
    return phone;
  }
  
  // 090/080/070 で始まる場合
  if (phone.match(/^0[789]0/)) {
    return phone.replace(/^0/, '+81');
  }
  
  // その他の場合は +81 を付けて返す
  return `+81${phone}`;
};

// 表示用電話番号のフォーマット
const formatDisplayPhone = (phone) => {
  if (!phone) return '';
  
  // 090-1234-5678 形式に変換
  if (phone.startsWith('+81')) {
    const number = phone.substring(3);
    if (number.length === 10) {
      return `${number.substring(0, 3)}-${number.substring(3, 7)}-${number.substring(7)}`;
    }
  }
  
  return phone;
};

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
    
    // エラーメッセージを詳細に取得
    let errorMessage = 'デバイスの初期化に失敗しました';
    if (error.response) {
      // サーバーからのエラーレスポンス
      const data = error.response.data;
      errorMessage += ': ' + (data.message || error.response.statusText || error.message);
    } else if (error.request) {
      // リクエストは送信されたが、レスポンスがなかった
      errorMessage += ': サーバーからの応答がありません。ネットワーク接続を確認してください。';
    } else {
      // その他のエラー
      errorMessage += ': ' + error.message;
    }
    
    showStatus('error', errorMessage);
    emit('call-failed', errorMessage);
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

// 自動発信（相互音声通話）
const autoCall = async () => {
  if (!props.phoneNumber || !device) {
    showStatus('error', '電話番号またはデバイスが設定されていません');
    return;
  }

  try {
    isConnecting.value = true;
    isCalling.value = true;
    showStatus('info', '発信中...');

    const formattedNumber = formatPhoneNumber(props.phoneNumber);

    // 通話を開始
    const params = {
      To: formattedNumber,
    };

    currentCall = markRaw(await device.connect({ params }));

    // 通話イベントリスナーを設定
    setupCallListeners();

  } catch (error) {
    console.error('Call error:', error);
    isConnecting.value = false;
    isCalling.value = false;
    showStatus('error', '通話の開始に失敗しました: ' + error.message);
    emit('call-failed', error.message);
  }
};

// 通話イベントリスナーの設定
const setupCallListeners = () => {
  if (!currentCall) return;

  currentCall.on('accept', () => {
    console.log('Call accepted');
    isConnecting.value = false;
    isCalling.value = false;
    isOnCall.value = true;
    callStartTime.value = Date.now();
    startDurationTimer();
    showStatus('success', '通話を開始しました');
    emit('call-started', { status: 'connected' });
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
    isConnecting.value = false;
    isCalling.value = false;
    showStatus('error', '通話が拒否されました');
    emit('call-failed', 'Call rejected');
  });

  currentCall.on('error', (error) => {
    console.error('Call error:', error);
    showStatus('error', '通話エラー: ' + error.message);
    endCall();
    emit('call-failed', error.message);
  });
};

// 発信キャンセル
const cancelCall = () => {
  isCalling.value = false;
  emit('call-cancelled');
};

// 通話終了
const hangup = () => {
  if (currentCall) {
    currentCall.disconnect();
  }
};

// 通話終了処理
const endCall = () => {
  isOnCall.value = false;
  isConnecting.value = false;
  isCalling.value = false;
  isMuted.value = false;
  currentCall = null;
  stopDurationTimer();
  
  // カウントダウン開始
  startCountdown();
  emit('call-completed', null);
};

// カウントダウン開始
const startCountdown = () => {
  isCountingDown.value = true;
  countdown.value = 3;
  
  countdownInterval = setInterval(() => {
    countdown.value--;
    
    if (countdown.value <= 0) {
      clearInterval(countdownInterval);
      isCountingDown.value = false;
    }
  }, 1000);
};

// カウントダウン停止
const stopCountdown = () => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
  isCountingDown.value = false;
  countdown.value = 3;
};

// ミュート切り替え
const toggleMute = () => {
  if (!currentCall) return;

  isMuted.value = !isMuted.value;
  currentCall.mute(isMuted.value);
  
  showStatus('info', isMuted.value ? 'ミュートしました' : 'ミュートを解除しました');
};

// 通話ステータス確認
const checkCallStatus = async () => {
  if (!callData.value || !callData.value.call_sid) {
    return;
  }

  try {
    const response = await axios.post(route('twilio-test.check-status'), {
      call_sid: callData.value.call_sid
    });
    
    const data = response.data;

    if (data.success) {
      callData.value = data.data;
      
      if (data.data.status === 'completed') {
        isOnCall.value = false;
        stopDurationTimer();
        showStatus('success', '通話が完了しました');
        emit('call-completed', data.data);
      } else if (data.data.status === 'busy' || data.data.status === 'no-answer') {
        isOnCall.value = false;
        stopDurationTimer();
        showStatus('error', `通話が${data.data.status === 'busy' ? '話し中' : '応答なし'}でした`);
        emit('call-failed', data.data.status);
      }
    }
  } catch (error) {
    // ステータス確認エラーは無視（重要ではない）
    console.warn('Call status check failed:', error);
  }
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
onMounted(async () => {
  // デバイスを初期化
  await initializeDevice();
  
  // デバイス準備完了後に自動発信
  if (deviceStatus.value === 'ready') {
    callTimeout = setTimeout(() => {
      autoCall();
    }, props.autoCallDelay);
  }
});

onUnmounted(() => {
  // クリーンアップ
  if (currentCall) {
    currentCall.disconnect();
  }
  if (device) {
    device.destroy();
  }
  if (callTimeout) {
    clearTimeout(callTimeout);
  }
  stopDurationTimer();
  stopCountdown();
});
</script>

<style scoped>
.twilio-auto-call-container {
  @apply w-full max-w-sm mx-auto text-center;
}

.call-in-progress,
.call-connecting,
.call-waiting,
.countdown-display {
  @apply py-6;
}

/* カウントダウン円形デザイン */
.countdown-circle {
  @apply w-20 h-20 mx-auto mb-6 relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
  animation: pulse 2s infinite;
}

.countdown-number {
  @apply text-3xl font-bold text-white;
}

/* 待機中アイコン */
.waiting-icon {
  @apply w-16 h-16 mx-auto mb-4 text-gray-400;
  opacity: 0.6;
}

/* ローディングドット */
.loading-dots {
  @apply flex justify-center space-x-1 mt-4;
}

.dot {
  @apply w-2 h-2 bg-gray-400 rounded-full;
  animation: bounce 1.4s infinite ease-in-out both;
}

.dot:nth-child(1) {
  animation-delay: -0.32s;
}

.dot:nth-child(2) {
  animation-delay: -0.16s;
}

/* ステータスメッセージ */
.status-message {
  @apply mt-4 p-3 rounded-lg border;
}

.status-message.success {
  @apply bg-green-50 border-green-200 text-green-700;
}

.status-message.error {
  @apply bg-red-50 border-red-200 text-red-700;
}

.status-message.info {
  @apply bg-blue-50 border-blue-200 text-blue-700;
}

.status-content {
  @apply flex items-center gap-2;
}

.status-icon {
  @apply w-4 h-4 flex-shrink-0;
}

.status-text {
  @apply text-sm font-medium;
}

/* アニメーション */
@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.8;
  }
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
