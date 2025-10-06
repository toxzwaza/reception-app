<template>
  <div class="twilio-call-container">
    <!-- 通話ボタン -->
    <button
      @click="makeCall"
      :disabled="isLoading || !phoneNumber"
      :class="[
        'twilio-call-button',
        isLoading ? 'loading' : '',
        !phoneNumber ? 'disabled' : ''
      ]"
    >
      <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
      </svg>
      
      <div class="button-content">
        <span v-if="isLoading" class="loading-text">発信中...</span>
        <span v-else-if="!phoneNumber" class="disabled-text">電話番号なし</span>
        <span v-else class="call-text">{{ buttonText }}</span>
      </div>
      
      <div v-if="isLoading" class="loading-spinner">
        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </button>

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
      
      <!-- 通話情報表示 -->
      <div v-if="callData && callData.call_sid" class="call-info">
        <p class="call-sid">Call SID: {{ callData.call_sid }}</p>
        <p class="call-status">Status: {{ callData.status }}</p>
      </div>
    </div>

    <!-- 電話番号情報 -->
    <div v-if="phoneNumber && showPhoneInfo" class="phone-info">
      <p class="phone-label">通話先:</p>
      <p class="phone-number">{{ formatDisplayPhone(phoneNumber) }}</p>
      <p v-if="contactName" class="contact-name">{{ contactName }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Props
const props = defineProps({
  phoneNumber: {
    type: String,
    default: ''
  },
  contactName: {
    type: String,
    default: ''
  },
  buttonText: {
    type: String,
    default: '電話をかける'
  },
  message: {
    type: String,
    default: '面接の方が到着しました。'
  },
  showPhoneInfo: {
    type: Boolean,
    default: true
  }
});

// Emits
const emit = defineEmits(['call-started', 'call-completed', 'call-failed']);

// State
const isLoading = ref(false);
const statusMessage = ref('');
const statusType = ref('');
const callData = ref(null);

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

// 通話発信
const makeCall = async () => {
  if (!props.phoneNumber) {
    showStatus('error', '電話番号が設定されていません');
    return;
  }

  isLoading.value = true;
  statusMessage.value = '';
  callData.value = null;

  try {
    const formattedNumber = formatPhoneNumber(props.phoneNumber);
    
    const response = await axios.post(route('twilio-test.make-call'), {
      to_number: formattedNumber,
      message: props.message
    });
    
    const data = response.data;

    if (data.success) {
      callData.value = data.data;
      showStatus('success', data.message);
      emit('call-started', data.data);
      
      // 5秒後にステータスを確認
      setTimeout(() => {
        checkCallStatus();
      }, 5000);
    } else {
      showStatus('error', data.message);
      emit('call-failed', data.message);
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message;
    showStatus('error', 'エラーが発生しました: ' + errorMessage);
    emit('call-failed', errorMessage);
  } finally {
    isLoading.value = false;
  }
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
        showStatus('success', '通話が完了しました');
        emit('call-completed', data.data);
      } else if (data.data.status === 'busy' || data.data.status === 'no-answer') {
        showStatus('error', `通話が${data.data.status === 'busy' ? '話し中' : '応答なし'}でした`);
        emit('call-failed', data.data.status);
      }
    }
  } catch (error) {
    // ステータス確認エラーは無視（重要ではない）
    console.warn('Call status check failed:', error);
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
.twilio-call-container {
  @apply w-full max-w-md mx-auto;
}

.twilio-call-button {
  @apply w-full flex items-center justify-center gap-3 px-6 py-4 bg-green-600 text-white rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl;
}

.twilio-call-button:hover:not(:disabled) {
  @apply bg-green-700 transform scale-105;
}

.twilio-call-button.loading {
  @apply bg-green-500 cursor-not-allowed;
}

.twilio-call-button.disabled {
  @apply bg-gray-400 cursor-not-allowed;
}

.twilio-call-button .icon {
  @apply w-6 h-6 flex-shrink-0;
}

.twilio-call-button .button-content {
  @apply flex-1;
}

.twilio-call-button .loading-text {
  @apply text-green-100;
}

.twilio-call-button .disabled-text {
  @apply text-gray-200;
}

.twilio-call-button .call-text {
  @apply text-white;
}

.twilio-call-button .loading-spinner {
  @apply w-5 h-5 flex-shrink-0;
}

.status-message {
  @apply mt-4 p-4 rounded-lg border-2;
}

.status-message.success {
  @apply bg-green-100 border-green-500 text-green-800;
}

.status-message.error {
  @apply bg-red-100 border-red-500 text-red-800;
}

.status-content {
  @apply flex items-center gap-2;
}

.status-icon {
  @apply w-5 h-5 flex-shrink-0;
}

.status-text {
  @apply font-medium;
}

.call-info {
  @apply mt-2 text-sm space-y-1;
}

.call-sid {
  @apply font-mono text-xs;
}

.call-status {
  @apply font-medium;
}

.phone-info {
  @apply mt-4 p-3 bg-gray-50 rounded-lg text-center;
}

.phone-label {
  @apply text-sm text-gray-600 mb-1;
}

.phone-number {
  @apply text-lg font-semibold text-gray-900;
}

.contact-name {
  @apply text-sm text-gray-700 mt-1;
}
</style>
