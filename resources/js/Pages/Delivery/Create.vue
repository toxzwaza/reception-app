<template>
  <ReceptionLayout
    title="納品業者受付"
    subtitle="必要な情報を入力し、書類を撮影してください"
    :steps="['情報入力', '書類撮影', '完了']"
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

          <!-- 書類種類選択 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-4">書類の種類</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button
                type="button"
                @click="selectDocumentType('納品書')"
                :class="[
                  'p-6 text-center rounded-lg border-2 transition-all duration-200',
                  form.delivery_type === '納品書'
                    ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200'
                    : 'border-gray-300 hover:border-indigo-300 hover:bg-indigo-50'
                ]"
              >
                <div class="text-xl font-semibold mb-2 text-gray-900">納品書</div>
                <div class="text-sm text-gray-600">納品書の電子印処理を行います</div>
              </button>

              <button
                type="button"
                @click="selectDocumentType('受領書')"
                :class="[
                  'p-6 text-center rounded-lg border-2 transition-all duration-200',
                  form.delivery_type === '受領書'
                    ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200'
                    : 'border-gray-300 hover:border-indigo-300 hover:bg-indigo-50'
                ]"
              >
                <div class="text-xl font-semibold mb-2 text-gray-900">受領書</div>
                <div class="text-sm text-gray-600">受領書の電子印処理を行います</div>
              </button>
            </div>
            <div v-if="errors.delivery_type" class="mt-2 text-sm text-red-600">
              {{ errors.delivery_type }}
            </div>
          </div>
        </div>

        <!-- 書類撮影セクション -->
        <div v-if="showCamera" class="mt-8">
          <fieldset>
            <legend class="text-base font-medium text-gray-900">書類撮影</legend>
            <p class="text-sm text-gray-500 mt-1">書類全体が枠内に収まるように撮影してください</p>

            <!-- カメラコンポーネント -->
            <Camera
              id="document"
              videoId="document-video"
              ariaLabelledby="document-section"
              :captureButtonLabel="`${form.delivery_type}を撮影`"
              guideFrameClass="w-[85%] h-[85%] border-2 border-indigo-500 relative"
              @capture="handleCapture"
              @cancel="handleCancel"
              @error="handleCameraError"
            />
          </fieldset>
        </div>

        <!-- プレビュー -->
        <div v-if="form.document_preview" class="mt-8">
          <div class="relative">
            <img
              :src="form.document_preview"
              :alt="form.delivery_type"
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
            v-if="!showCamera && !form.document_preview"
            type="button"
            variant="primary"
            :disabled="!form.delivery_type"
            @click="startCamera"
          >
            書類を撮影
          </Button>
          <Button
            v-if="form.document_preview"
            type="submit"
            variant="primary"
            :disabled="processing"
          >
            {{ processing ? '処理中...' : '電子印を押す' }}
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
  delivery_type: '',
  staff_member_id: '',
  document_image: null,
  document_preview: null,
});

// 状態管理
const processing = ref(false);
const showCamera = ref(false);
const cameraError = ref('');

// 書類種類の選択
const selectDocumentType = (type) => {
  form.delivery_type = type;
};

// カメラ関連の処理
const startCamera = () => {
  if (!form.delivery_type) {
    cameraError.value = '書類の種類を選択してください';
    return;
  }
  showCamera.value = true;
  cameraError.value = '';
};

const handleCapture = (dataUrl) => {
  form.document_preview = dataUrl;
  form.document_image = dataURLtoFile(dataUrl, 'document.jpg');
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
  form.document_preview = null;
  form.document_image = null;
  startCamera();
};

// フォーム送信
const submitForm = () => {
  if (!form.document_image) {
    cameraError.value = '書類を撮影してください';
    return;
  }

  processing.value = true;
  form.post(route('delivery.store'), {
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