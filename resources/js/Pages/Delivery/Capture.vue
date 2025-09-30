<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h1 class="text-2xl font-bold text-center mb-8">
              {{ delivery_type }}の撮影
            </h1>

            <!-- カメラビュー -->
            <div class="mb-8">
              <div v-if="!form.document_preview" class="relative">
                <video
                  ref="video"
                  id="document-video"
                  class="w-full max-w-2xl mx-auto rounded-lg"
                  autoplay
                  playsinline
                  muted
                ></video>

                <!-- 撮影ガイド -->
                <div class="absolute top-0 left-0 right-0 bottom-0 pointer-events-none">
                  <div class="w-full h-full flex items-center justify-center">
                    <div class="w-[85%] h-[85%] border-2 border-blue-500 relative">
                      <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-blue-500"></div>
                      <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-blue-500"></div>
                      <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-blue-500"></div>
                      <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-blue-500"></div>
                    </div>
                  </div>
                </div>

                <!-- カメラ切り替え -->
                <div v-if="cameras.length > 1" class="mt-4">
                  <label for="camera-select" class="block text-sm font-medium text-gray-700 mb-2">カメラを選択</label>
                  <select
                    id="camera-select"
                    name="camera-select"
                    v-model="currentCamera"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    @change="switchCamera"
                  >
                    <option value="">カメラを選択してください</option>
                    <option v-for="camera in cameras" :key="camera.deviceId" :value="camera.deviceId">
                      {{ camera.label || `カメラ ${cameras.indexOf(camera) + 1}` }}
                    </option>
                  </select>
                </div>

                <!-- 撮影ボタン -->
                <div class="mt-4 flex justify-center space-x-4">
                  <button
                    type="button"
                    @click="captureImage"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                  >
                    撮影
                  </button>
                </div>
              </div>

              <!-- プレビュー -->
              <div v-else class="space-y-4">
                <div class="relative">
                  <img
                    :src="form.document_preview"
                    :alt="delivery_type"
                    class="max-w-full rounded-lg shadow-lg"
                  />
                  <button
                    type="button"
                    @click="retakeImage"
                    class="absolute top-2 right-2 p-2 bg-white rounded-full shadow-md hover:bg-gray-100"
                  >
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </button>
                </div>

                <!-- 送信ボタン -->
                <div class="flex justify-center space-x-4">
                  <button
                    type="button"
                    @click="submitForm"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    :disabled="processing"
                  >
                    {{ processing ? '処理中...' : '電子印を押す' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- エラーメッセージ -->
            <div v-if="cameraError" class="text-red-600 text-center mb-4 p-4 bg-red-50 rounded-lg">
              <p class="font-medium">カメラの起動に失敗しました</p>
              <p class="text-sm mt-2">{{ cameraError }}</p>
              <button
                type="button"
                @click="initCamera"
                class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
              >
                再試行
              </button>
            </div>

            <!-- 戻るボタン -->
            <div class="text-center mt-4">
              <Link
                :href="route('delivery.create')"
                class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                preserve-scroll
              >
                戻る
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  company_name: {
    type: String,
    required: true
  },
  delivery_type: {
    type: String,
    required: true
  },
  staff_member_id: {
    type: String,
    required: true
  }
});

const form = useForm({
  company_name: props.company_name,
  delivery_type: props.delivery_type,
  staff_member_id: props.staff_member_id,
  document_image: null,
  document_preview: null,
});

const processing = ref(false);
const video = ref(null);
const currentCamera = ref('');
const cameras = ref([]);
const cameraError = ref('');
let stream = null;

// 利用可能なカメラを取得
const getCameras = async () => {
  try {
    const devices = await navigator.mediaDevices.enumerateDevices();
    const videoDevices = devices.filter(device => device.kind === 'videoinput');
    cameras.value = videoDevices;

    if (videoDevices.length === 0) {
      throw new Error('カメラが見つかりません');
    }

    // 背面カメラを優先して選択
    const backCamera = videoDevices.find(camera => 
      camera.label.toLowerCase().includes('back') || 
      camera.label.toLowerCase().includes('rear') ||
      camera.label.toLowerCase().includes('環境') ||
      camera.label.toLowerCase().includes('背面')
    );
    currentCamera.value = backCamera?.deviceId || videoDevices[0].deviceId;

    return true;
  } catch (error) {
    console.error('カメラの列挙エラー:', error);
    cameraError.value = `カメラの検出に失敗しました: ${error.message}`;
    return false;
  }
};

// カメラの開始
const startCamera = async () => {
  try {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
    }

    stream = await navigator.mediaDevices.getUserMedia({
      video: {
        deviceId: currentCamera.value ? { exact: currentCamera.value } : undefined,
        facingMode: !currentCamera.value ? 'environment' : undefined,
        width: { ideal: 3840 }, // 4K
        height: { ideal: 2160 }
      },
      audio: false
    });

    if (video.value) {
      video.value.srcObject = stream;
      await new Promise((resolve) => {
        video.value.onloadedmetadata = () => {
          video.value.play().then(resolve);
        };
      });
    }

    cameraError.value = '';
  } catch (error) {
    console.error('カメラの起動エラー:', error);
    cameraError.value = `カメラの起動に失敗しました: ${error.message}`;
  }
};

// カメラの切り替え
const switchCamera = async () => {
  await startCamera();
};

// 画像のキャプチャ
const captureImage = () => {
  if (!video.value) return;

  const canvas = document.createElement('canvas');
  canvas.width = video.value.videoWidth;
  canvas.height = video.value.videoHeight;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video.value, 0, 0, canvas.width, canvas.height);

  // 画像をBase64形式で取得（高品質）
  const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
  form.document_preview = dataUrl;
  
  // Base64をBlobに変換してフォームデータに設定
  const blob = dataURLtoFile(dataUrl, 'document.jpg');
  form.document_image = blob;

  stopCamera();
};

// 撮影のやり直し
const retakeImage = () => {
  form.document_preview = null;
  form.document_image = null;
  startCamera();
};

// カメラの停止
const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
};

// フォーム送信
const submitForm = () => {
  processing.value = true;
  form.post(route('delivery.store'), {
    onSuccess: () => {
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};

// カメラの初期化
const initCamera = async () => {
  cameraError.value = '';
  
  try {
    // カメラの権限を確認
    const permission = await navigator.permissions.query({ name: 'camera' });
    if (permission.state === 'denied') {
      throw new Error('カメラの使用が拒否されています。ブラウザの設定でカメラへのアクセスを許可してください。');
    }

    const camerasAvailable = await getCameras();
    if (camerasAvailable) {
      await startCamera();
    }
  } catch (error) {
    console.error('カメラの初期化エラー:', error);
    cameraError.value = error.message;
  }
};

// Data URL を File オブジェクトに変換
const dataURLtoFile = (dataurl, filename) => {
  const arr = dataurl.split(',');
  const mime = arr[0].match(/:(.*?);/)[1];
  const bstr = atob(arr[1]);
  let n = bstr.length;
  const u8arr = new Uint8Array(n);
  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }
  return new File([u8arr], filename, { type: mime });
};

// コンポーネントのマウント時
onMounted(() => {
  initCamera();
});

// コンポーネントのクリーンアップ
onUnmounted(() => {
  stopCamera();
});
</script>

<style scoped>
#document-video {
  min-height: 300px;
  background-color: #000;
}
</style>