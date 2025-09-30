<template>
  <div>
    <!-- カメラビュー -->
    <div v-if="showCamera" class="mb-4">
      <div class="relative">
        <div class="video-container">
          <video
            ref="video"
            :id="videoId"
            :name="videoId"
            class="rounded-lg"
            autoplay
            playsinline
            muted
            :aria-labelledby="ariaLabelledby"
          ></video>
        </div>

        <!-- 撮影ガイド -->
        <div class="absolute top-0 left-0 right-0 bottom-0 pointer-events-none" aria-hidden="true">
          <div class="w-full h-full flex items-center justify-center">
            <div :class="guideFrameClass">
              <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-blue-500"></div>
              <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-blue-500"></div>
              <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-blue-500"></div>
              <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-blue-500"></div>
            </div>
          </div>
        </div>

        <!-- カメラ切り替え -->
        <div v-if="cameras.length > 1" class="mt-4">
          <label :for="'camera-select-' + id" class="block text-sm font-medium text-gray-700 mb-2">カメラを選択</label>
          <select
            :id="'camera-select-' + id"
            :name="'camera-select-' + id"
            v-model="currentCamera"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            @change="switchCamera"
          >
            <option value="" disabled>カメラを選択してください</option>
            <option
              v-for="(camera, index) in cameras"
              :key="camera.deviceId"
              :value="camera.deviceId"
              :id="'camera-option-' + id + '-' + index"
            >
              {{ camera.label || `カメラ ${index + 1}` }}
            </option>
          </select>
        </div>

        <!-- カメラ操作ボタン -->
        <div class="mt-4 flex justify-center space-x-4">
          <button
            type="button"
            :id="'capture-button-' + id"
            :name="'capture-button-' + id"
            @click="capture"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
            :aria-label="captureButtonLabel"
          >
            撮影
          </button>
          <button
            type="button"
            :id="'cancel-button-' + id"
            :name="'cancel-button-' + id"
            @click="cancel"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
            aria-label="カメラをキャンセル"
          >
            キャンセル
          </button>
        </div>
      </div>
    </div>

    <!-- エラーメッセージ -->
    <div v-if="cameraError" class="mt-2 text-red-600 text-sm" role="alert" :id="'camera-error-' + id">
      {{ cameraError }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  videoId: {
    type: String,
    required: true,
  },
  ariaLabelledby: {
    type: String,
    required: true,
  },
  captureButtonLabel: {
    type: String,
    required: true,
  },
  guideFrameClass: {
    type: String,
    default: 'w-96 h-56 border-2 border-blue-500 relative',
  },
});

const emit = defineEmits(['capture', 'cancel', 'error']);

const video = ref(null);
const currentCamera = ref('');
const cameras = ref([]);
const cameraError = ref('');
const showCamera = ref(true);
let stream = null;

// 利用可能なカメラを取得
const getCameras = async () => {
  try {
    if (!navigator.mediaDevices?.enumerateDevices) {
      throw new Error('お使いのブラウザはカメラをサポートしていません');
    }

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
    handleError(`カメラの検出に失敗しました: ${error.message}`);
    return false;
  }
};

// カメラの開始
const startCamera = async () => {
  try {
    if (!navigator.mediaDevices?.getUserMedia) {
      throw new Error('お使いのブラウザはカメラをサポートしていません');
    }

    // カメラの権限を確認
    const permission = await navigator.permissions.query({ name: 'camera' });
    if (permission.state === 'denied') {
      throw new Error('カメラの使用が拒否されています。ブラウザの設定でカメラへのアクセスを許可してください。');
    }

    const camerasAvailable = await getCameras();
    if (!camerasAvailable) {
      return;
    }

    // 既存のストリームを停止
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
    }

    // カメラの設定
    const constraints = {
      video: {
        deviceId: currentCamera.value ? { exact: currentCamera.value } : undefined,
        facingMode: !currentCamera.value ? 'environment' : undefined,
        width: { ideal: 1920 },
        height: { ideal: 1080 },
        frameRate: { ideal: 30 }
      },
      audio: false
    };

    // カメラストリームの取得
    stream = await navigator.mediaDevices.getUserMedia(constraints);

    // ビデオ要素にストリームを設定
    if (video.value) {
      video.value.srcObject = stream;
      await new Promise((resolve, reject) => {
        video.value.onloadedmetadata = () => {
          video.value.play()
            .then(resolve)
            .catch(error => {
              console.error('ビデオ再生エラー:', error);
              reject(new Error('カメラの起動に失敗しました'));
            });
        };
        video.value.onerror = (error) => {
          console.error('ビデオ要素エラー:', error);
          reject(new Error('ビデオの初期化に失敗しました'));
        };
      });
    } else {
      throw new Error('ビデオ要素が見つかりません');
    }

    showCamera.value = true;
    cameraError.value = '';
  } catch (error) {
    console.error('カメラの起動エラー:', error);
    handleError(`カメラの起動に失敗しました: ${error.message}`);
    showCamera.value = false;
    stopCamera();
  }
};

// カメラの切り替え
const switchCamera = async () => {
  try {
    stopCamera();
    await startCamera();
  } catch (error) {
    console.error('カメラ切り替えエラー:', error);
    handleError('カメラの切り替えに失敗しました');
  }
};

// 画像のキャプチャ
const capture = () => {
  if (!video.value) {
    handleError('カメラが起動していません');
    return;
  }

  try {
    const canvas = document.createElement('canvas');
    const videoElem = video.value;
    
    canvas.width = videoElem.videoWidth;
    canvas.height = videoElem.videoHeight;
    
    const ctx = canvas.getContext('2d');
    if (!ctx) {
      throw new Error('キャンバスコンテキストの取得に失敗しました');
    }

    ctx.drawImage(videoElem, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
    
    stopCamera();
    emit('capture', dataUrl);
  } catch (error) {
    console.error('画像キャプチャエラー:', error);
    handleError('画像の取得に失敗しました');
  }
};

// カメラのキャンセル
const cancel = () => {
  stopCamera();
  emit('cancel');
};

// カメラの停止
const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => {
      track.stop();
      stream.removeTrack(track);
    });
    stream = null;
  }
  
  if (video.value && video.value.srcObject) {
    video.value.srcObject = null;
  }
  
  showCamera.value = false;
};

// エラーハンドリング
const handleError = (message) => {
  cameraError.value = message;
  emit('error', message);
};

// コンポーネントのマウント時
onMounted(() => {
  startCamera();
});

// コンポーネントのクリーンアップ
onUnmounted(() => {
  stopCamera();
});
</script>

<style scoped>
.video-container {
  position: relative;
  width: 100%;
  max-width: 640px;
  margin: 0 auto;
  background-color: #000;
}

.video-container::before {
  content: "";
  display: block;
  padding-top: 75%; /* 4:3 アスペクト比 */
}

.video-container video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
