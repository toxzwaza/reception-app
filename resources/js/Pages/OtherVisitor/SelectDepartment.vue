<template>
  <ReceptionLayout 
    title="その他の方" 
    subtitle="訪問先部署を選択してください"
    :steps="['訪問者情報入力', '部署選択', '完了']"
    :current-step="1"
  >
    <div class="p-8">
          <!-- 訪問者情報の確認 -->
          <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">訪問者情報</h3>
            <div class="space-y-2 text-sm">
              <p><span class="font-medium">社名:</span> {{ visitorData.company_name }}</p>
              <p><span class="font-medium">氏名:</span> {{ visitorData.visitor_name }}</p>
              <p><span class="font-medium">人数:</span> {{ visitorData.number_of_people }}名</p>
              <p><span class="font-medium">要件:</span> {{ visitorData.purpose }}</p>
            </div>
          </div>

          <form @submit.prevent="submitDepartment" class="space-y-6">
            <!-- 部署選択 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">
                訪問先部署を選択してください <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <button
                  v-for="dept in departments"
                  :key="dept.id"
                  type="button"
                  @click="selectedDepartment = dept.id"
                  :class="[
                    'p-4 rounded-lg border-2 transition-all text-center',
                    selectedDepartment === dept.id
                      ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold'
                      : 'border-gray-200 hover:border-gray-300 text-gray-700'
                  ]"
                >
                  {{ dept.name }}
                </button>
              </div>
            </div>

          <!-- ボタン -->
          <div class="flex gap-4">
            <button
              type="button"
              @click="goBack"
              class="flex-1 py-3 px-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200"
            >
              戻る
            </button>
            <button
              type="submit"
              :disabled="!selectedDepartment || processing"
              class="flex-1 py-3 px-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
            >
              {{ processing ? '送信中...' : '確定' }}
            </button>
          </div>
        </form>
      </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

const props = defineProps({
  visitorData: {
    type: Object,
    required: true
  },
  departments: {
    type: Array,
    required: true
  }
});

const selectedDepartment = ref(null);
const processing = ref(false);

const goBack = () => {
  window.history.back();
};

const submitDepartment = () => {
  if (!selectedDepartment.value || processing.value) return;
  
  processing.value = true;
  
  router.post(route('other-visitor.store'), {
    ...props.visitorData,
    group_id: selectedDepartment.value
  }, {
    onFinish: () => {
      processing.value = false;
    }
  });
};
</script>

