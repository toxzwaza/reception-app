<template>
  <ReceptionLayout
    title="来訪者受付"
    subtitle="ご来訪の情報を入力してください"
    :steps="['情報入力', '確認', '完了']"
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

          <Input
            id="visitor_name"
            name="visitor_name"
            type="text"
            label="お名前"
            v-model="form.visitor_name"
            :error="errors.visitor_name"
            required
            placeholder="山田 太郎"
          />

          <Input
            id="phone"
            name="phone"
            type="tel"
            label="電話番号"
            v-model="form.phone"
            :error="errors.phone"
            placeholder="090-1234-5678"
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

        <!-- 名刺撮影セクション -->
        <div class="mt-8">
          <fieldset>
            <legend class="text-base font-medium text-gray-900">名刺</legend>
            <p class="text-sm text-gray-500 mt-1">名刺を撮影またはアップロードしてください</p>

            <!-- カメラコンポーネント -->
            <Camera
              v-if="showCamera"
              id="business-card"
              videoId="business-card-video"
              ariaLabelledby="business-card-section"
              captureButtonLabel="名刺を撮影"
              guideFrameClass="w-96 h-56 border-2 border-blue-500 relative"
              @capture="handleCapture"
              @cancel="handleCancel"
              @error="handleCameraError"
            />

            <!-- カメラ起動ボタンとファイルアップロード -->
            <div v-if="!showCamera && !form.business_card_preview" class="mt-4 space-y-4">
              <Button
                type="button"
                variant="outline"
                fullWidth
                @click="startCamera"
              >
                <template #icon-left>
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </template>
                カメラを起動して撮影する
              </Button>

              <div class="relative">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center">
                  <span class="px-2 bg-white text-sm text-gray-500">または</span>
                </div>
              </div>

              <div class="mt-4">
                <input
                  type="file"
                  id="business_card_file"
                  name="business_card_file"
                  accept="image/*"
                  @change="handleFileUpload"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
              </div>
            </div>

            <!-- プレビュー -->
            <div v-if="form.business_card_preview" class="mt-4">
              <div class="relative">
                <img
                  :src="form.business_card_preview"
                  alt="名刺プレビュー"
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
          </fieldset>
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
            type="submit"
            variant="primary"
            :disabled="processing"
          >
            {{ processing ? '送信中...' : '送信' }}
          </Button>
        </div>
      </form>
    </FormSection>
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
  visitor_name: '',
  phone: '',
  business_card_image: null,
  business_card_preview: null,
  staff_member_id: '',
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
  form.business_card_preview = dataUrl;
  form.business_card_image = dataURLtoFile(dataUrl, 'business_card.jpg');
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
  form.business_card_preview = null;
  form.business_card_image = null;
  startCamera();
};

// ファイルアップロード処理
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) { // 5MB制限
      cameraError.value = 'ファイルサイズは5MB以下にしてください';
      return;
    }

    form.business_card_image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      form.business_card_preview = e.target.result;
    };
    reader.onerror = () => {
      cameraError.value = 'ファイルの読み込みに失敗しました';
    };
    reader.readAsDataURL(file);
  }
};

// フォーム送信
const submitForm = () => {
  processing.value = true;
  form.post(route('visitor.store'), {
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