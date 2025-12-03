<template>
  <ReceptionLayout
    title="電子印付き伝票"
    subtitle="集荷伝票"
    :steps="['納品・集荷選択', '情報入力', '完了']"
    :current-step="2"
    :show-back-button="false"
  >
    <div class="p-12">
      <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">電子印付き集荷伝票</h2>

            <!-- 伝票情報 -->
            <div class="mb-6">
              <div class="bg-gray-50 px-4 py-3 rounded-lg">
                <dl class="grid grid-cols-1 gap-2">
                  <div class="flex justify-between">
                    <dt class="text-gray-600">伝票種類</dt>
                    <dd class="font-medium">集荷伝票</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-gray-600">受付日時</dt>
                    <dd class="font-medium">{{ formatDate(pickup.picked_up_at) }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- 電子印付き伝票画像 -->
            <div class="mb-8">
              <div class="bg-white rounded-lg shadow overflow-hidden">
                <img
                  :src="sealedSlipUrl"
                  alt="集荷伝票"
                  class="w-full h-auto"
                />
              </div>
            </div>

      <!-- 印刷ボタン -->
      <div class="flex justify-center space-x-4 max-w-3xl mx-auto">
        <button
          @click="printDocument"
          class="px-8 py-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-lg shadow-lg"
        >
          伝票を印刷
        </button>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

const props = defineProps({
  pickup: {
    type: Object,
    required: true,
  },
  sealedSlipUrl: {
    type: String,
    required: true,
  },
});

// 日付フォーマット
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// 伝票印刷
const printDocument = () => {
  const printWindow = window.open('', '_blank');
  printWindow.document.write('<!DOCTYPE html>' +
    '<html>' +
      '<head>' +
        '<title>集荷伝票印刷</title>' +
        '<style>' +
          'body {' +
            'margin: 0;' +
            'padding: 20px;' +
          '}' +
          '.document-info {' +
            'margin-bottom: 20px;' +
            'font-family: sans-serif;' +
          '}' +
          '.document-image {' +
            'width: 100%;' +
            'height: auto;' +
          '}' +
        '</style>' +
      '</head>' +
      '<body>' +
        '<div class="document-info">' +
          '<h2>集荷伝票</h2>' +
          '<p>受付日時: ' + formatDate(props.pickup.picked_up_at) + '</p>' +
        '</div>' +
        '<img src="' + props.sealedSlipUrl + '" class="document-image" />' +
        '<script>' +
          'window.onload = function() {' +
            'window.print();' +
            'window.close();' +
          '};' +
        '</' + 'script>' +
      '</body>' +
    '</html>');
  printWindow.document.close();
};
</script>
