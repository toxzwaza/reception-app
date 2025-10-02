<template>
  <ReceptionLayout
    title="集荷業者受付"
    subtitle="受領書を撮影してください"
  >
    <div class="p-8">
      <form @submit.prevent="submitForm">
        <div class="max-w-4xl mx-auto">
          <!-- カメラ表示 -->
          <div v-if="showCamera">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">受領書を撮影してください</h2>
            <div class="relative bg-black rounded-2xl overflow-hidden mb-6" style="height: 500px;">
              <video
                ref="videoElement"
                autoplay
                playsinline
                class="w-full h-full object-cover"
              ></video>
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="border-4 border-indigo-500 border-dashed rounded-xl" style="width: 85%; height: 85%;"></div>
              </div>
            </div>
            
            <!-- ヒント -->
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
              <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800">撮影のヒント</h3>
                  <ul class="mt-2 text-sm text-blue-700 list-disc pl-5 space-y-1">
                    <li>受領書全体が枠内に収まるようにしてください</li>
                    <li>明るい場所で撮影してください</li>
                    <li>できるだけ真上から撮影してください</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="flex gap-4">
              <button
                type="button"
                @click="handleCancel"
                class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 text-lg"
              >
                キャンセル
              </button>
              <button
                type="button"
                @click="captureSlip"
                class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 text-lg"
              >
                撮影
              </button>
            </div>
          </div>

          <!-- プレビュー -->
          <div v-else-if="form.slip_preview">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">撮影内容の確認</h3>
            <div class="relative mb-6">
              <img
                :src="form.slip_preview"
                alt="受領書"
                class="w-full rounded-lg shadow-lg"
              />
            </div>
            
            <div class="flex gap-4">
              <button
                type="button"
                @click="retakeImage"
                class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 text-lg"
              >
                撮り直す
              </button>
              <button
                type="submit"
                :disabled="processing"
                class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
              >
                {{ processing ? '処理中...' : '電子印を押す' }}
              </button>
            </div>
          </div>

          <!-- 撮影開始画面 -->
          <div v-else class="text-center py-12">
            <div class="w-32 h-32 mx-auto mb-6 text-orange-500">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">受領書を撮影してください</h2>
            <p class="text-gray-600 mb-8">撮影ボタンを押してカメラを起動してください</p>
            
            <button
              type="button"
              @click="startCamera"
              class="px-12 py-6 bg-indigo-600 text-white text-xl rounded-lg font-semibold hover:bg-indigo-700 shadow-lg"
            >
              撮影を開始
            </button>
          </div>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="cameraError" class="mt-4 text-center text-sm text-red-600" role="alert">
          {{ cameraError }}
        </div>
      </form>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

const props = defineProps({
  errors: {
    type: Object,
    default: () => ({}),
  },
});

// フォームデータ
const form = ref({
  slip_image: null,
  slip_preview: null,
});

// 状態管理
const processing = ref(false);
const showCamera = ref(false);
const cameraError = ref('');
const videoElement = ref(null);
let stream = null;

onUnmounted(() => {
  stopCamera();
});

// カメラ開始
const startCamera = async () => {
  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment' }
    });
    
    showCamera.value = true;
    cameraError.value = '';
    
    setTimeout(() => {
      if (videoElement.value) {
        videoElement.value.srcObject = stream;
      }
    }, 100);
  } catch (error) {
    console.error('カメラの起動に失敗しました:', error);
    cameraError.value = 'カメラの起動に失敗しました';
  }
};

// カメラ停止
const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
};

// 伝票を撮影
const captureSlip = () => {
  const video = videoElement.value;
  if (!video) return;

  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const context = canvas.getContext('2d');
  context.drawImage(video, 0, 0);

  const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
  form.value.slip_preview = dataUrl;
  form.value.slip_image = dataURLtoFile(dataUrl, 'slip.jpg');
  
  stopCamera();
  showCamera.value = false;
};

// キャンセル
const handleCancel = () => {
  stopCamera();
  showCamera.value = false;
};

// 撮り直し
const retakeImage = () => {
  form.value.slip_preview = null;
  form.value.slip_image = null;
  startCamera();
};

// フォーム送信
const submitForm = () => {
  if (!form.value.slip_image) {
    cameraError.value = '受領書を撮影してください';
    return;
  }

  processing.value = true;
  
  router.post(route('pickup.store'), form.value, {
    onSuccess: () => {
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};

// Data URL を File オブジェクトに変換
const dataURLtoFile = (dataurl, filename) => {
  try {
    const arr = dataurl.split(',');
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }
    return new File([u8arr], filename, { type: mime });
  } catch (error) {
    console.error('ファイル変換エラー:', error);
    throw new Error('画像の処理に失敗しました');
  }
};
</script>