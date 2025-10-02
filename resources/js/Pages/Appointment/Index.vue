<template>
  <ReceptionLayout title="アポイントアリの方" subtitle="QRコードを読み取るか、受付番号を入力してください">
    <div class="p-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- QRコード読み取り -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4 text-center">QRコード読み取り</h2>
          <div class="space-y-4">
            <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="height: 400px;">
              <video ref="videoElement" autoplay playsinline class="w-full h-full object-cover"></video>
              <div v-if="qrScanned" class="absolute inset-0 bg-green-500 bg-opacity-20 flex items-center justify-center">
                <div class="bg-white rounded-lg p-4 shadow-lg">
                  <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <p class="text-center mt-2 font-semibold text-gray-900">読み取り成功</p>
                </div>
              </div>
            </div>
            <p class="text-sm text-gray-600 text-center">QRコードをカメラに向けてください</p>
          </div>
        </div>

        <!-- 受付番号入力 -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4 text-center">受付番号入力</h2>
          <form @submit.prevent="submitReceptionNumber" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">受付番号（4桁）</label>
              <div class="flex gap-2 justify-center">
                <input
                  v-for="i in 4"
                  :key="i"
                  :ref="el => digitInputs[i - 1] = el"
                  v-model="receptionNumber[i - 1]"
                  @input="handleDigitInput(i - 1)"
                  @keydown="handleKeydown($event, i - 1)"
                  type="text"
                  maxlength="1"
                  pattern="[0-9]"
                  class="w-16 h-20 text-center text-3xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  inputmode="numeric"
                />
              </div>
            </div>

            <button
              type="submit"
              :disabled="!isReceptionNumberComplete || processing"
              class="w-full py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors text-lg"
            >
              {{ processing ? '処理中...' : '受付' }}
            </button>

          </form>
        </div>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import jsQR from 'jsqr';

const videoElement = ref(null);
const qrScanned = ref(false);
const receptionNumber = ref(['', '', '', '']);
const digitInputs = ref([]);
const processing = ref(false);
let stream = null;
let animationFrameId = null;

const isReceptionNumberComplete = computed(() => {
  return receptionNumber.value.every(digit => digit !== '');
});

onMounted(() => {
  startQRScanner();
});

onUnmounted(() => {
  stopQRScanner();
});

const startQRScanner = async () => {
  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment' }
    });
    
    if (videoElement.value) {
      videoElement.value.srcObject = stream;
      scanQRCode();
    }
  } catch (error) {
    console.error('カメラの起動に失敗しました:', error);
  }
};

const stopQRScanner = () => {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
  }
};

const scanQRCode = () => {
  const video = videoElement.value;
  if (!video) return;

  const canvas = document.createElement('canvas');
  const context = canvas.getContext('2d');

  const scan = () => {
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      
      const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
      const code = jsQR(imageData.data, imageData.width, imageData.height);

      if (code) {
        handleQRCodeDetected(code.data);
        return;
      }
    }
    animationFrameId = requestAnimationFrame(scan);
  };

  scan();
};

const handleQRCodeDetected = (qrData) => {
  qrScanned.value = true;
  stopQRScanner();
  
  // QRコードデータを処理
  router.post(route('appointment.check-in-qr'), {
    qr_data: qrData
  });
};

const handleDigitInput = (index) => {
  const value = receptionNumber.value[index];
  
  // 数字以外を削除
  if (value && !/^\d$/.test(value)) {
    receptionNumber.value[index] = '';
    return;
  }

  // 次の入力欄にフォーカス
  if (value && index < 3) {
    digitInputs.value[index + 1]?.focus();
  }
};

const handleKeydown = (event, index) => {
  // Backspaceで前の入力欄に戻る
  if (event.key === 'Backspace' && !receptionNumber.value[index] && index > 0) {
    digitInputs.value[index - 1]?.focus();
  }
};

const submitReceptionNumber = () => {
  if (!isReceptionNumberComplete.value || processing.value) return;
  
  processing.value = true;
  const number = receptionNumber.value.join('');
  
  router.post(route('appointment.check-in-number'), {
    reception_number: number
  }, {
    onFinish: () => {
      processing.value = false;
    }
  });
};
</script>

