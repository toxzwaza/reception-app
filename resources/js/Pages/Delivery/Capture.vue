<template>
  <ReceptionLayout
    title="納品業者受付"
    :subtitle="delivery_type + 'を撮影してください'"
    :steps="['納品・集荷選択', '情報入力', '完了']"
    :current-step="1"
  >
    <div class="p-12">
      <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">
        {{ delivery_type }}を撮影してください
      </h2>

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

                <!-- カウントダウン表示 -->
                <div v-if="isCountingDown" class="absolute top-4 right-4 pointer-events-none z-10">
                  <div class="text-center bg-black bg-opacity-80 rounded-full p-6">
                    <div class="text-6xl font-bold text-white mb-2 animate-pulse">
                      {{ countdown }}
                    </div>
                    <div class="text-sm text-white">撮影中...</div>
                  </div>
                </div>

                <!-- 撮影ボタン -->
                <div class="mt-4 flex justify-center space-x-4">
                  <button
                    type="button"
                    @click="startCountdown"
                    :disabled="isCountingDown"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                  >
                    {{ isCountingDown ? '撮影中...' : '撮影開始' }}
                  </button>
                </div>
              </div>

              <!-- プレビュー -->
              <div v-else class="space-y-4">
                <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">こちらの画像でよろしいですか？</h3>
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

                <!-- 注意文 -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                  <div class="flex">
                    <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-red-800">撮影内容の確認</h3>
                      <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                        <li>テキストがきちんと可読可能か確認してください</li>
                        <li>手や指で隠れていないか確認してください</li>
                        <li>書類全体が写っているか確認してください</li>
                        <li>明るさが適切か確認してください</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- 送信ボタン -->
                <div class="flex justify-center space-x-4">
                  <button
                    type="button"
                    @click="submitForm"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    :disabled="processing"
                  >
                    {{ processing ? '処理中...' : '確定' }}
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
      <div class="text-center mt-8">
        <Link
          :href="route('delivery.create')"
          class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-lg"
          preserve-scroll
        >
          戻る
        </Link>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

const props = defineProps({
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
const countdown = ref(0);
const isCountingDown = ref(false);
let stream = null;
let countdownTimer = null;

// 利用可能なカメラを取得
const getCameras = async () => {
  try {
    const devices = await navigator.mediaDevices.enumerateDevices();
    const videoDevices = devices.filter(device => device.kind === 'videoinput');
    cameras.value = videoDevices;

    if (videoDevices.length === 0) {
      throw new Error('カメラが見つかりません');
    }

    // フロントカメラ（内カメラ）を優先して選択
    const frontCamera = videoDevices.find(camera => 
      camera.label.toLowerCase().includes('front') || 
      camera.label.toLowerCase().includes('user') ||
      camera.label.toLowerCase().includes('内') ||
      camera.label.toLowerCase().includes('フロント')
    );
    currentCamera.value = frontCamera?.deviceId || videoDevices[0].deviceId;

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
      stream = null;
    }

    console.log('カメラを開始しています...');

    // シンプルなカメラ設定
    const constraints = {
      video: {
        facingMode: 'user' // 内カメラを優先
      },
      audio: false
    };

    // 特定のカメラが選択されている場合はdeviceIdを使用
    if (currentCamera.value) {
      constraints.video = {
        deviceId: { exact: currentCamera.value }
      };
    }

    stream = await navigator.mediaDevices.getUserMedia(constraints);
    console.log('カメラストリームを取得しました:', stream);

    if (video.value) {
      video.value.srcObject = stream;
      console.log('ビデオ要素にストリームを設定しました');
      
      // ビデオの再生を確実に開始
      video.value.onloadedmetadata = () => {
        console.log('ビデオメタデータが読み込まれました');
        video.value.play()
          .then(() => {
            console.log('ビデオの再生を開始しました');
          })
          .catch(error => {
            console.error('ビデオの再生に失敗しました:', error);
          });
      };
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

// カウントダウン開始
const startCountdown = () => {
  countdown.value = 3;
  isCountingDown.value = true;
  
  countdownTimer = setInterval(() => {
    countdown.value--;
    if (countdown.value <= 0) {
      clearInterval(countdownTimer);
      isCountingDown.value = false;
      captureImage();
    }
  }, 1000);
};

// 画像のキャプチャ
const captureImage = () => {
  if (!video.value) {
    console.error('ビデオ要素が見つかりません');
    return;
  }

  try {
    // ビデオの準備状態を確認
    console.log('ビデオの状態:', {
      videoWidth: video.value.videoWidth,
      videoHeight: video.value.videoHeight,
      readyState: video.value.readyState,
      paused: video.value.paused,
      ended: video.value.ended
    });
    
    if (video.value.videoWidth === 0 || video.value.videoHeight === 0) {
      throw new Error('ビデオがまだ準備できていません。しばらく待ってから再度お試しください。');
    }
    
    if (video.value.readyState < 2) {
      throw new Error('ビデオの読み込みが完了していません。しばらく待ってから再度お試しください。');
    }

    const canvas = document.createElement('canvas');
    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;
    const ctx = canvas.getContext('2d');
    
    if (!ctx) {
      throw new Error('キャンバスコンテキストの取得に失敗しました');
    }
    
    // ビデオをキャンバスに描画
    ctx.drawImage(video.value, 0, 0, canvas.width, canvas.height);
    
    // キャンバスの内容を確認
    const imageData = ctx.getImageData(0, 0, Math.min(10, canvas.width), Math.min(10, canvas.height));
    const hasContent = imageData.data.some(value => value !== 0);
    console.log('キャンバスに内容があるか:', hasContent);

    // 画像をBase64形式で取得（高品質）
    const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
    console.log('データURLを生成しました:', dataUrl ? '成功' : '失敗');
    console.log('データURLの長さ:', dataUrl ? dataUrl.length : 0);
    console.log('データURLの先頭:', dataUrl ? dataUrl.substring(0, 50) : 'null');
    
    if (!dataUrl || dataUrl === 'data:,') {
      throw new Error('画像データの生成に失敗しました。カメラ映像が正しく表示されていない可能性があります。');
    }
    
    form.document_preview = dataUrl;
    
    // タイムスタンプ付きファイル名を生成
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
    const filename = `document_${timestamp}.jpg`;
    
    try {
      const blob = dataURLtoFile(dataUrl, filename);
      form.document_image = blob;
      console.log('ファイル変換成功:', filename);
    } catch (fileError) {
      console.error('ファイル変換エラー:', fileError);
      throw fileError;
    }

    stopCamera();
  } catch (error) {
    console.error('撮影エラー:', error);
    cameraError.value = `撮影に失敗しました: ${error.message}`;
  }
};

// 撮影のやり直し
const retakeImage = () => {
  form.document_preview = null;
  form.document_image = null;
  if (countdownTimer) {
    clearInterval(countdownTimer);
    countdownTimer = null;
  }
  isCountingDown.value = false;
  countdown.value = 0;
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
  try {
    if (!dataurl || typeof dataurl !== 'string') {
      throw new Error('無効なデータURLです');
    }
    
    const arr = dataurl.split(',');
    if (arr.length !== 2) {
      throw new Error('データURLの形式が正しくありません');
    }
    
    const mimeMatch = arr[0].match(/:(.*?);/);
    if (!mimeMatch) {
      throw new Error('MIMEタイプの取得に失敗しました');
    }
    
    const mime = mimeMatch[1];
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }
    return new File([u8arr], filename, { type: mime });
  } catch (error) {
    console.error('ファイル変換エラー:', error);
    console.error('データURL:', dataurl);
    throw new Error(`画像の処理に失敗しました: ${error.message}`);
  }
};

// コンポーネントのマウント時
onMounted(() => {
  initCamera();
});

// コンポーネントのクリーンアップ
onUnmounted(() => {
  stopCamera();
  if (countdownTimer) {
    clearInterval(countdownTimer);
  }
});
</script>

<style scoped>
#document-video {
  min-height: 300px;
  background-color: #000;
}
</style>