<template>
  <ReceptionLayout 
    title="アポイントなしの方" 
    subtitle="訪問者情報をご入力ください"
    :steps="['訪問者情報入力', '部署選択', '完了']"
    :current-step="0"
  >
    <!-- 入力方法選択画面 -->
    <div v-if="currentStep === 'select-method'" class="p-12">
      <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">入力方法を選択してください</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
        <!-- 名刺撮影 -->
        <div 
          @click="selectMethod('camera')"
          class="bg-white rounded-xl border-2 border-gray-200 hover:border-indigo-500 hover:shadow-2xl transition-all duration-200 cursor-pointer group p-12"
        >
          <div class="flex flex-col items-center text-center">
            <div class="w-32 h-32 mb-6 text-indigo-500 group-hover:scale-110 transition-transform duration-200">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">名刺撮影</h3>
            <p class="text-gray-600">名刺をカメラで撮影して<br>自動入力します</p>
          </div>
        </div>

        <!-- キーボード入力 -->
        <div 
          @click="selectMethod('keyboard')"
          class="bg-white rounded-xl border-2 border-gray-200 hover:border-green-500 hover:shadow-2xl transition-all duration-200 cursor-pointer group p-12"
        >
          <div class="flex flex-col items-center text-center">
            <div class="w-32 h-32 mb-6 text-green-500 group-hover:scale-110 transition-transform duration-200">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">キーボード入力</h3>
            <p class="text-gray-600">質問に答える形式で<br>情報を入力します</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 名刺撮影画面 -->
    <div v-else-if="currentStep === 'camera'" class="p-12">
      <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">名刺を撮影してください</h2>
      
      <div class="max-w-3xl mx-auto">
        <div class="relative bg-black rounded-2xl overflow-hidden mb-6" style="height: 500px;">
          <video
            ref="videoElement"
            autoplay
            playsinline
            class="w-full h-full object-cover"
          ></video>
        </div>

        <div class="flex gap-4">
          <button
            @click="backToMethodSelect"
            class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 text-lg"
          >
            戻る
          </button>
          <button
            @click="captureBusinessCard"
            class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 text-lg"
          >
            撮影
          </button>
        </div>
      </div>
    </div>

    <!-- キーボード入力画面（質問形式） -->
    <div v-else-if="currentStep === 'company-name'" class="p-8" style="padding-bottom: 450px;">
      <div class="w-full max-w-2xl mx-auto">
        <h2 class="text-4xl font-bold text-gray-900 mb-8 text-center">会社名を入力してください</h2>
        <input
          v-model="form.company_name"
          type="text"
          readonly
          @click="focusInput"
          class="w-full text-2xl px-6 py-4 rounded-lg border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center bg-white cursor-text"
          placeholder="株式会社〇〇"
        />
        <div class="flex gap-4 mt-8">
          <button
            @click="backToMethodSelect"
            class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 text-lg"
          >
            戻る
          </button>
          <button
            @click="nextStep"
            :disabled="!form.company_name"
            class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
          >
            次へ
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="currentStep === 'visitor-name'" class="p-8" style="padding-bottom: 450px;">
      <div class="w-full max-w-2xl mx-auto">
        <h2 class="text-4xl font-bold text-gray-900 mb-8 text-center">お名前を入力してください</h2>
        <input
          v-model="form.visitor_name"
          type="text"
          readonly
          @click="focusInput"
          class="w-full text-2xl px-6 py-4 rounded-lg border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center bg-white cursor-text"
          placeholder="山田 太郎"
        />
        <div class="flex gap-4 mt-8">
          <button
            @click="previousStep"
            class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 text-lg"
          >
            戻る
          </button>
          <button
            @click="nextStep"
            :disabled="!form.visitor_name"
            class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
          >
            次へ
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="currentStep === 'number-of-people'" class="p-8" style="padding-bottom: 300px;">
      <div class="w-full max-w-2xl mx-auto">
        <h2 class="text-4xl font-bold text-gray-900 mb-8 text-center">訪問人数を入力してください</h2>
        <input
          v-model="form.number_of_people"
          type="text"
          readonly
          @click="focusInput"
          class="w-full text-2xl px-6 py-4 rounded-lg border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center bg-white cursor-text"
          placeholder="1"
        />
        <div class="flex gap-4 mt-8">
          <button
            @click="previousStep"
            class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 text-lg"
          >
            戻る
          </button>
          <button
            @click="nextStep"
            :disabled="!form.number_of_people || parseInt(form.number_of_people) < 1"
            class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
          >
            次へ
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="currentStep === 'purpose'" class="p-8" style="padding-bottom: 450px;">
      <div class="w-full max-w-2xl mx-auto">
        <h2 class="text-4xl font-bold text-gray-900 mb-8 text-center">訪問の目的を入力してください</h2>
        <textarea
          v-model="form.purpose"
          readonly
          @click="focusInput"
          rows="6"
          class="w-full text-xl px-6 py-4 rounded-lg border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-text"
          placeholder="例：新規サービスのご提案"
        ></textarea>
        <div class="flex gap-4 mt-8">
          <button
            @click="previousStep"
            class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 text-lg"
          >
            戻る
          </button>
          <button
            @click="submitForm"
            :disabled="!form.purpose || processing"
            class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
          >
            {{ processing ? '処理中...' : '次へ' }}
          </button>
        </div>
      </div>
    </div>

    <!-- 仮想キーボード -->
    <VirtualKeyboard
      v-if="showKeyboard"
      :type="keyboardType"
      @input="handleKeyboardInput"
      @backspace="handleBackspace"
      @enter="handleEnter"
      @space="handleSpace"
      @dakuten="handleDakuten"
      @handakuten="handleHandakuten"
    />
  </ReceptionLayout>
</template>

<script setup>
import { ref, onUnmounted, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import VirtualKeyboard from '@/Components/VirtualKeyboard.vue';

const form = ref({
  company_name: '',
  visitor_name: '',
  number_of_people: '',
  purpose: '',
  business_card_image: null,
});

const processing = ref(false);
const currentStep = ref('select-method'); // select-method, camera, company-name, visitor-name, number-of-people, purpose
const videoElement = ref(null);
let stream = null;

// 仮想キーボード表示フラグ
const showKeyboard = computed(() => {
  return ['company-name', 'visitor-name', 'number-of-people', 'purpose'].includes(currentStep.value);
});

// キーボードタイプ
const keyboardType = computed(() => {
  return currentStep.value === 'number-of-people' ? 'number' : 'text';
});

onUnmounted(() => {
  stopCamera();
});

// 入力方法を選択
const selectMethod = (method) => {
  if (method === 'camera') {
    currentStep.value = 'camera';
    startCamera();
  } else {
    currentStep.value = 'company-name';
  }
};

// カメラ開始
const startCamera = async () => {
  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment' }
    });
    
    setTimeout(() => {
      if (videoElement.value) {
        videoElement.value.srcObject = stream;
      }
    }, 100);
  } catch (error) {
    console.error('カメラの起動に失敗しました:', error);
    alert('カメラの起動に失敗しました。キーボード入力をご利用ください。');
    backToMethodSelect();
  }
};

// カメラ停止
const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
};

// 名刺を撮影
const captureBusinessCard = () => {
  const video = videoElement.value;
  if (!video) return;

  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const context = canvas.getContext('2d');
  context.drawImage(video, 0, 0);

  canvas.toBlob((blob) => {
    form.value.business_card_image = new File([blob], 'business_card.jpg', { type: 'image/jpeg' });
    stopCamera();
    
    // TODO: OCR処理で名刺から情報を抽出
    // 今は仮のデータを入力
    form.value.company_name = '（名刺から自動入力）';
    form.value.visitor_name = '（名刺から自動入力）';
    
    alert('名刺を撮影しました。情報が自動入力されました。\n\n※OCR機能は今後実装予定です。\n現在は手動で修正してください。');
    
    // キーボード入力の最初のステップへ
    currentStep.value = 'company-name';
  }, 'image/jpeg', 0.9);
};

// 入力方法選択画面に戻る
const backToMethodSelect = () => {
  stopCamera();
  currentStep.value = 'select-method';
  // フォームをリセット
  form.value = {
    company_name: '',
    visitor_name: '',
    number_of_people: 1,
    purpose: '',
    business_card_image: null,
  };
};

// 入力欄のフォーカス（仮想キーボード表示のため）
const focusInput = () => {
  // readonly入力欄がクリックされたときの処理
  // 実際には何もしない（仮想キーボードは常に表示されているため）
};

// 仮想キーボードからの入力
const handleKeyboardInput = (value) => {
  if (currentStep.value === 'company-name') {
    form.value.company_name += value;
  } else if (currentStep.value === 'visitor-name') {
    form.value.visitor_name += value;
  } else if (currentStep.value === 'number-of-people') {
    form.value.number_of_people += value;
  } else if (currentStep.value === 'purpose') {
    form.value.purpose += value;
  }
};

// バックスペース
const handleBackspace = () => {
  if (currentStep.value === 'company-name') {
    form.value.company_name = form.value.company_name.slice(0, -1);
  } else if (currentStep.value === 'visitor-name') {
    form.value.visitor_name = form.value.visitor_name.slice(0, -1);
  } else if (currentStep.value === 'number-of-people') {
    form.value.number_of_people = form.value.number_of_people.toString().slice(0, -1);
  } else if (currentStep.value === 'purpose') {
    form.value.purpose = form.value.purpose.slice(0, -1);
  }
};

// Enter（確定）
const handleEnter = () => {
  if (currentStep.value === 'purpose') {
    submitForm();
  } else {
    nextStep();
  }
};

// スペース
const handleSpace = () => {
  if (currentStep.value === 'company-name') {
    form.value.company_name += ' ';
  } else if (currentStep.value === 'visitor-name') {
    form.value.visitor_name += ' ';
  } else if (currentStep.value === 'purpose') {
    form.value.purpose += ' ';
  }
};

// 濁点・半濁点変換マップ
const dakutenMap = {
  'か': 'が', 'き': 'ぎ', 'く': 'ぐ', 'け': 'げ', 'こ': 'ご',
  'さ': 'ざ', 'し': 'じ', 'す': 'ず', 'せ': 'ぜ', 'そ': 'ぞ',
  'た': 'だ', 'ち': 'ぢ', 'つ': 'づ', 'て': 'で', 'と': 'ど',
  'は': 'ば', 'ひ': 'び', 'ふ': 'ぶ', 'へ': 'べ', 'ほ': 'ぼ',
};

const handakutenMap = {
  'は': 'ぱ', 'ひ': 'ぴ', 'ふ': 'ぷ', 'へ': 'ぺ', 'ほ': 'ぽ',
};

// 濁点変換
const handleDakuten = () => {
  let targetField = '';
  if (currentStep.value === 'company-name') {
    targetField = 'company_name';
  } else if (currentStep.value === 'visitor-name') {
    targetField = 'visitor_name';
  } else if (currentStep.value === 'purpose') {
    targetField = 'purpose';
  }

  if (targetField && form.value[targetField].length > 0) {
    const lastChar = form.value[targetField].slice(-1);
    if (dakutenMap[lastChar]) {
      form.value[targetField] = form.value[targetField].slice(0, -1) + dakutenMap[lastChar];
    }
  }
};

// 半濁点変換
const handleHandakuten = () => {
  let targetField = '';
  if (currentStep.value === 'company-name') {
    targetField = 'company_name';
  } else if (currentStep.value === 'visitor-name') {
    targetField = 'visitor_name';
  } else if (currentStep.value === 'purpose') {
    targetField = 'purpose';
  }

  if (targetField && form.value[targetField].length > 0) {
    const lastChar = form.value[targetField].slice(-1);
    if (handakutenMap[lastChar]) {
      form.value[targetField] = form.value[targetField].slice(0, -1) + handakutenMap[lastChar];
    }
  }
};

// 次のステップへ
const nextStep = () => {
  if (currentStep.value === 'company-name') {
    if (!form.value.company_name) return;
    currentStep.value = 'visitor-name';
  } else if (currentStep.value === 'visitor-name') {
    if (!form.value.visitor_name) return;
    currentStep.value = 'number-of-people';
  } else if (currentStep.value === 'number-of-people') {
    const num = parseInt(form.value.number_of_people);
    if (!num || num < 1) return;
    currentStep.value = 'purpose';
  }
};

// 前のステップへ
const previousStep = () => {
  if (currentStep.value === 'visitor-name') {
    currentStep.value = 'company-name';
  } else if (currentStep.value === 'number-of-people') {
    currentStep.value = 'visitor-name';
  } else if (currentStep.value === 'purpose') {
    currentStep.value = 'number-of-people';
  }
};

// フォーム送信
const submitForm = () => {
  if (processing.value) return;
  if (!form.value.purpose) return;
  
  processing.value = true;
  
  // 人数を数値に変換
  const submitData = {
    ...form.value,
    number_of_people: parseInt(form.value.number_of_people) || 1
  };
  
  // 部署選択画面へ遷移（フォームデータを渡す）
  router.visit(route('other-visitor.select-department'), {
    method: 'post',
    data: submitData,
    preserveState: false,
    onFinish: () => {
      processing.value = false;
    }
  });
};

// currentStepが変わったときにカメラを停止
watch(currentStep, (newStep, oldStep) => {
  if (oldStep === 'camera' && newStep !== 'camera') {
    stopCamera();
  }
});
</script>


