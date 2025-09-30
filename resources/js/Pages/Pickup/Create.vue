<template>
  <ReceptionLayout
    title="集荷業者受付"
    subtitle="必要な情報を入力し、伝票を撮影してください"
    :steps="['情報入力', '伝票撮影', '完了']"
    :currentStep="0"
  >
    <FormSection>
      <form @submit.prevent="submitForm">
        <!-- 基本情報 -->
        <div class="space-y-6">
          <Input
            id="company_name"
            name="company_name"
            type="text"
            label="会社名"
            v-model="form.company_name"
            :error="errors.company_name"
            required
            placeholder="株式会社〇〇"
          />

          <Select
            id="staff_member_id"
            name="staff_member_id"
            label="担当者"
            v-model="form.staff_member_id"
            :error="errors.staff_member_id"
            required
            placeholder="担当者を選択してください"
          >
            <option
              v-for="staff in staffMembers"
              :key="staff.id"
              :value="staff.id"
            >
              {{ staff.name }} ({{ staff.department }})
            </option>
          </Select>
        </div>

        <!-- 伝票撮影セクション -->
        <div v-if="showCamera" class="mt-8">
          <fieldset>
            <legend class="text-base font-medium text-gray-900">集荷伝票の撮影</legend>
            <p class="text-sm text-gray-500 mt-1">伝票全体が枠内に収まるように撮影してください</p>

            <!-- カメラコンポーネント -->
            <Camera
              id="pickup-slip"
              videoId="pickup-slip-video"
              ariaLabelledby="slip-section"
              captureButtonLabel="集荷伝票を撮影"
              guideFrameClass="w-[85%] h-[85%] border-2 border-indigo-500 relative"
              @capture="handleCapture"
              @cancel="handleCancel"
              @error="handleCameraError"
            />
          </fieldset>
        </div>

        <!-- プレビュー -->
        <div v-if="form.slip_preview" class="mt-8">
          <div class="relative">
            <img
              :src="form.slip_preview"
              alt="集荷伝票プレビュー"
              class="max-w-full rounded-lg shadow-lg"
            />
            <Button
              type="button"
              variant="secondary"
              size="sm"
              @click="retakeImage"
              class="absolute top-2 right-2"
            >
              撮り直す
            </Button>
          </div>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="cameraError" class="mt-2 text-sm text-red-600" role="alert">
          {{ cameraError }}
        </div>

        <!-- 送信ボタン -->
        <div class="mt-8 flex justify-end space-x-4">
          <Button
            type="button"
            variant="outline"
            :href="route('home')"
          >
            キャンセル
          </Button>
          <Button
            v-if="!showCamera && !form.slip_preview"
            type="button"
            variant="primary"
            @click="startCamera"
          >
            伝票を撮影
          </Button>
          <Button
            v-if="form.slip_preview"
            type="submit"
            variant="primary"
            :disabled="processing"
          >
            {{ processing ? '処理中...' : '電子印を押す' }}
          </Button>
        </div>
      </form>
    </FormSection>

    <!-- 撮影のヒント -->
    <div v-if="showCamera" class="mt-6">
      <div class="bg-blue-50 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">撮影のヒント</h3>
            <div class="mt-2 text-sm text-blue-700">
              <ul class="list-disc pl-5 space-y-1">
                <li>明るい場所で撮影してください</li>
                <li>伝票が影で隠れないようにしてください</li>
                <li>伝票全体が枠内に収まるようにしてください</li>
                <li>できるだけ真上から撮影してください</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import FormSection from '@/Components/UI/FormSection.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Camera from '@/Components/Camera.vue';

const props = defineProps({
  staffMembers: {
    type: Array,
    required: true,
    default: () => [],
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

// フォームの初期化
const form = useForm({
  company_name: '',
  staff_member_id: '',
  slip_image: null,
  slip_preview: null,
});

// 状態管理
const processing = ref(false);
const showCamera = ref(false);
const cameraError = ref('');

// カメラ関連の処理
const startCamera = () => {
  showCamera.value = true;
  cameraError.value = '';
};

const handleCapture = (dataUrl) => {
  form.slip_preview = dataUrl;
  form.slip_image = dataURLtoFile(dataUrl, 'pickup_slip.jpg');
  showCamera.value = false;
};

const handleCancel = () => {
  showCamera.value = false;
};

const handleCameraError = (error) => {
  cameraError.value = error;
  showCamera.value = false;
};

const retakeImage = () => {
  form.slip_preview = null;
  form.slip_image = null;
  startCamera();
};

// フォーム送信
const submitForm = () => {
  if (!form.slip_image) {
    cameraError.value = '集荷伝票を撮影してください';
    return;
  }

  processing.value = true;
  form.post(route('pickup.store'), {
    onSuccess: () => {
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
    preserveScroll: true,
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