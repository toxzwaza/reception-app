<template>
  <ReceptionLayout
    title="QRコード読取"
    subtitle="QRコードを枠内に表示してください"
  >
    <div class="max-w-2xl mx-auto">
      <div class="bg-white rounded-lg overflow-hidden">
        <!-- カメラビュー -->
        <div class="relative">
          <Camera
            id="qr-scanner"
            videoId="qr-video"
            ariaLabelledby="qr-scanner-title"
            captureButtonLabel="QRコードを読み取る"
            guideFrameClass="w-64 h-64 border-2 border-indigo-500 relative"
            @capture="handleCapture"
            @cancel="handleCancel"
            @error="handleCameraError"
          />

          <!-- QRコードガイド -->
          <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="text-center text-white text-sm px-4 py-2 bg-black bg-opacity-50 rounded-lg">
              QRコードを枠内に合わせてください
            </div>
          </div>
        </div>

        <!-- エラーメッセージ -->
        <div
          v-if="error"
          class="bg-red-50 border-l-4 border-red-400 p-4 mt-4"
          role="alert"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">
                {{ error }}
              </p>
            </div>
          </div>
        </div>

        <!-- 説明テキスト -->
        <div class="p-4 bg-gray-50 border-t border-gray-200">
          <h3 class="text-sm font-medium text-gray-900">QRコードが読み取れない場合</h3>
          <div class="mt-2 text-sm text-gray-500">
            <ul class="list-disc pl-5 space-y-1">
              <li>QRコードが汚れていないか確認してください</li>
              <li>明るい場所で試してください</li>
              <li>カメラの位置を調整してください</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- 戻るボタン -->
      <div class="mt-6 text-center">
        <Button
          variant="outline"
          :href="route('home')"
        >
          <template #icon-left>
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </template>
          トップに戻る
        </Button>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Camera from '@/Components/Camera.vue';
import jsQR from 'jsqr';

const error = ref('');

// QRコード読み取り処理
const processQRCode = (imageData) => {
  const code = jsQR(imageData.data, imageData.width, imageData.height);
  if (code) {
    try {
      const visitorData = JSON.parse(code.data);
      router.post(route('visitor.check-in'), visitorData);
      return true;
    } catch (e) {
      error.value = '無効なQRコードです';
    }
  }
  return false;
};

// 撮影完了時の処理
const handleCapture = (dataUrl) => {
  const img = new Image();
  img.onload = () => {
    const canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;
    
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    if (!processQRCode(imageData)) {
      error.value = 'QRコードを検出できませんでした';
    }
  };
  img.src = dataUrl;
};

// カメラキャンセル時の処理
const handleCancel = () => {
  router.get(route('home'));
};

// カメラエラー時の処理
const handleCameraError = (message) => {
  error.value = message;
};
</script>