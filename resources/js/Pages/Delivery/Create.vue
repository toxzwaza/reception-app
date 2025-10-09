<template>
  <ReceptionLayout
    title="納品業者受付"
    subtitle="書類を撮影してください"
    :steps="['納品・集荷選択', '情報入力', '完了']"
    :current-step="1"
  >
    <div class="p-12">
      <form @submit.prevent="submitForm">
        <!-- 書類種類選択（未選択時のみ表示） -->
        <div v-if="!form.delivery_type" class="mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">書類の種類を選択してください</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <button
              type="button"
              @click="selectDocumentType('納品書')"
              class="bg-white p-12 text-center rounded-xl border-2 border-gray-200 hover:border-indigo-500 hover:shadow-2xl transition-all duration-200 cursor-pointer group"
            >
              <div class="flex flex-col items-center">
                <div class="w-32 h-32 mb-6 text-indigo-500 group-hover:scale-110 transition-transform duration-200">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div class="text-2xl font-bold mb-3 text-gray-900">納品書</div>
                <div class="text-gray-600">納品書の電子印処理を行います</div>
              </div>
            </button>

            <button
              type="button"
              @click="selectDocumentType('受領書')"
              class="bg-white p-12 text-center rounded-xl border-2 border-gray-200 hover:border-green-500 hover:shadow-2xl transition-all duration-200 cursor-pointer group"
            >
              <div class="flex flex-col items-center">
                <div class="w-32 h-32 mb-6 text-green-500 group-hover:scale-110 transition-transform duration-200">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                  </svg>
                </div>
                <div class="text-2xl font-bold mb-3 text-gray-900">受領書</div>
                <div class="text-gray-600">受領書の電子印処理を行います</div>
              </div>
            </button>
          </div>
          <div v-if="errors.delivery_type" class="mt-4 text-center text-sm text-red-600">
            {{ errors.delivery_type }}
          </div>
        </div>

        <!-- 選択中の書類種類表示と変更ボタン -->
        <div v-else class="mb-8 max-w-4xl mx-auto">
          <div :class="[
            'bg-white rounded-xl border-2 p-6 flex items-center justify-between shadow-lg',
            form.delivery_type === '納品書' ? 'border-indigo-500' : 'border-green-500'
          ]">
            <div class="flex items-center">
              <div :class="[
                'w-12 h-12 mr-4',
                form.delivery_type === '納品書' ? 'text-indigo-600' : 'text-green-600'
              ]">
                <svg v-if="form.delivery_type === '納品書'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
              </div>
              <div>
                <div class="text-sm text-gray-500">書類種類</div>
                <div class="text-xl font-bold text-gray-900">{{ form.delivery_type }}</div>
              </div>
            </div>
            <button
              type="button"
              @click="changeDocumentType"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold transition-colors duration-200"
            >
              変更
            </button>
          </div>
        </div>

        <!-- カメラ表示またはプレビュー -->
        <div v-if="form.delivery_type" class="max-w-4xl mx-auto">
          <!-- カメラ表示 -->
          <div v-if="showCamera">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">
              {{ form.delivery_type }}を撮影してください
            </h3>
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
              
              <!-- カウントダウン表示 -->
              <div v-if="isCountingDown" class="absolute top-4 right-4 pointer-events-none z-10">
                <div class="text-center bg-black bg-opacity-80 rounded-full p-6">
                  <div class="text-6xl font-bold text-white mb-2 animate-pulse">
                    {{ countdown }}
                  </div>
                  <div class="text-sm text-white">撮影中...</div>
                </div>
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
                    <li>書類全体が枠内に収まるようにしてください</li>
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
                :disabled="isCountingDown"
                class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
              >
                キャンセル
              </button>
              <button
                type="button"
                @click="startCountdown"
                :disabled="isCountingDown"
                class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
              >
                {{ isCountingDown ? '撮影中...' : '撮影開始' }}
              </button>
            </div>
          </div>

          <!-- プレビュー -->
          <div v-else-if="form.document_preview">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">こちらの画像でよろしいですか？</h3>
            <div class="relative mb-6">
              <img
                :src="form.document_preview"
                :alt="form.delivery_type"
                class="w-full rounded-lg shadow-lg"
              />
            </div>
            
            <!-- 注意文 -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
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
                {{ processing ? '処理中...' : '確定' }}
              </button>
            </div>
          </div>

          <!-- 撮影開始ボタン -->
          <div v-else class="text-center">
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
import { ref, onUnmounted, nextTick } from 'vue';
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
  delivery_type: '',
  document_image: null,
  document_preview: null,
});

// 状態管理
const processing = ref(false);
const showCamera = ref(false);
const cameraError = ref('');
const videoElement = ref(null);
const countdown = ref(0);
const isCountingDown = ref(false);
let stream = null;
let countdownTimer = null;

onUnmounted(() => {
  stopCamera();
  if (countdownTimer) {
    clearInterval(countdownTimer);
  }
});

// 書類種類の選択
const selectDocumentType = (type) => {
  form.value.delivery_type = type;
};

// 書類種類の変更
const changeDocumentType = () => {
  // カメラを停止
  stopCamera();
  if (countdownTimer) {
    clearInterval(countdownTimer);
    countdownTimer = null;
  }
  isCountingDown.value = false;
  countdown.value = 0;
  showCamera.value = false;
  
  // フォームをリセット
  form.value.delivery_type = '';
  form.value.document_image = null;
  form.value.document_preview = null;
  cameraError.value = '';
};

// カメラ開始
const startCamera = async () => {
  if (!form.value.delivery_type) {
    cameraError.value = '書類の種類を選択してください';
    return;
  }
  
  try {
    // 既存のストリームを停止
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
    
    console.log('カメラを開始しています...');
    
    // シンプルなカメラ設定
    stream = await navigator.mediaDevices.getUserMedia({
      video: { 
        facingMode: 'user'
      },
      audio: false
    });
    
    console.log('カメラストリームを取得しました():', stream);
    
    showCamera.value = true;
    cameraError.value = '';
    
    // DOMの更新を待ってからビデオ要素にアクセス
    await nextTick();
    
    // ビデオ要素の存在を確認
    if (!videoElement.value) {
      console.error('ビデオ要素が見つかりません');
      cameraError.value = 'ビデオ要素の初期化に失敗しました';
      return;
    }
    
    console.log('ビデオ要素を確認しました:', videoElement.value);
    
    // ストリームを設定
    videoElement.value.srcObject = stream;
    console.log('ビデオ要素にストリームを設定しました');
    
    // ビデオの再生を開始
    try {
      await videoElement.value.play();
      console.log('ビデオの再生を開始しました');
    } catch (error) {
      console.error('ビデオの再生に失敗しました:', error);
    }
    
    // ビデオの準備完了を待ってからカウントダウン開始
    waitForVideoReady();
  } catch (error) {
    console.error('カメラの起動に失敗しました:', error);
    cameraError.value = `カメラの起動に失敗しました: ${error.message}`;
  }
};

// ビデオの準備完了を待機
const waitForVideoReady = () => {
  let checkCount = 0;
  const maxChecks = 100; // 最大10秒間チェック
  let countdownStarted = false; // カウントダウンが既に開始されたかを追跡
  
  const checkVideo = () => {
    checkCount++;
    const video = videoElement.value;
    
    console.log(`ビデオ準備チェック ${checkCount}/${maxChecks}:`, {
      videoExists: !!video,
      videoWidth: video?.videoWidth || 0,
      videoHeight: video?.videoHeight || 0,
      readyState: video?.readyState || 0,
      paused: video?.paused,
      ended: video?.ended
    });
    
    if (video && video.videoWidth > 0 && video.videoHeight > 0 && video.readyState >= 2 && !countdownStarted) {
      console.log('ビデオが準備完了しました:', {
        videoWidth: video.videoWidth,
        videoHeight: video.videoHeight,
        readyState: video.readyState
      });
      countdownStarted = true;
      // 2秒後にカウントダウン開始
      setTimeout(() => {
        if (!isCountingDown.value) {
          console.log('カウントダウンを開始します');
          startCountdown();
        }
      }, 2000);
    } else if (checkCount >= maxChecks) {
      console.error('ビデオの準備がタイムアウトしました');
      cameraError.value = 'カメラの準備に時間がかかりすぎています。ページを再読み込みしてください。';
    } else {
      // 100ms後に再チェック
      setTimeout(checkVideo, 100);
    }
  };
  
  checkVideo();
};

// カウントダウン開始
const startCountdown = () => {
  // 既存のタイマーをクリア
  if (countdownTimer) {
    clearInterval(countdownTimer);
    countdownTimer = null;
  }
  
  countdown.value = 3;
  isCountingDown.value = true;
  
  console.log('カウントダウンを3から開始します');
  
  countdownTimer = setInterval(() => {
    countdown.value--;
    console.log('カウントダウン:', countdown.value);
    if (countdown.value <= 0) {
      clearInterval(countdownTimer);
      countdownTimer = null;
      isCountingDown.value = false;
      console.log('カウントダウン終了、撮影を開始します');
      captureDocument();
    }
  }, 1000);
};

// カメラ停止
const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
};

// 書類を撮影
const captureDocument = () => {
  const video = videoElement.value;
  if (!video) {
    console.error('ビデオ要素が見つかりません');
    return;
  }

  try {
    // ビデオの準備状態を確認
    console.log('ビデオの状態:', {
      videoWidth: video.videoWidth,
      videoHeight: video.videoHeight,
      readyState: video.readyState,
      paused: video.paused,
      ended: video.ended
    });
    
    if (video.videoWidth === 0 || video.videoHeight === 0) {
      throw new Error('ビデオがまだ準備できていません。しばらく待ってから再度お試しください。');
    }
    
    if (video.readyState < 2) {
      throw new Error('ビデオの読み込みが完了していません。しばらく待ってから再度お試しください。');
    }

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    
    if (!context) {
      throw new Error('キャンバスコンテキストの取得に失敗しました');
    }
    
    // ビデオをキャンバスに描画
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // キャンバスの内容を確認
    const imageData = context.getImageData(0, 0, Math.min(10, canvas.width), Math.min(10, canvas.height));
    const hasContent = imageData.data.some(value => value !== 0);
    console.log('キャンバスに内容があるか:', hasContent);
    
    const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
    console.log('データURLを生成しました:', dataUrl ? '成功' : '失敗');
    console.log('データURLの長さ:', dataUrl ? dataUrl.length : 0);
    console.log('データURLの先頭:', dataUrl ? dataUrl.substring(0, 50) : 'null');
    
    if (!dataUrl || dataUrl === 'data:,') {
      throw new Error('画像データの生成に失敗しました。カメラ映像が正しく表示されていない可能性があります。');
    }
    
    form.value.document_preview = dataUrl;
    
    // タイムスタンプ付きファイル名を生成
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
    const filename = `document_${timestamp}.jpg`;
    
    try {
      form.value.document_image = dataURLtoFile(dataUrl, filename);
      console.log('ファイル変換成功:', filename);
    } catch (fileError) {
      console.error('ファイル変換エラー:', fileError);
      throw fileError;
    }
    
    stopCamera();
    showCamera.value = false;
  } catch (error) {
    console.error('撮影エラー:', error);
    cameraError.value = `撮影に失敗しました: ${error.message}`;
  }
};

// キャンセル
const handleCancel = () => {
  if (countdownTimer) {
    clearInterval(countdownTimer);
    countdownTimer = null;
  }
  isCountingDown.value = false;
  countdown.value = 0;
  stopCamera();
  showCamera.value = false;
};

// 撮り直し
const retakeImage = () => {
  form.value.document_preview = null;
  form.value.document_image = null;
  if (countdownTimer) {
    clearInterval(countdownTimer);
    countdownTimer = null;
  }
  isCountingDown.value = false;
  countdown.value = 0;
  startCamera();
};

// フォーム送信
const submitForm = () => {
  if (!form.value.document_image) {
    cameraError.value = '書類を撮影してください';
    return;
  }

  processing.value = true;
  
  router.post(route('delivery.store'), form.value, {
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
</script>