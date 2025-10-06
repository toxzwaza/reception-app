<template>
  <div class="min-h-screen bg-gray-100">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h1 class="text-2xl font-bold text-center mb-8">電子印付き{{ delivery.delivery_type }}</h1>

            <!-- 書類情報 -->
            <div class="mb-6">
              <div class="bg-gray-50 px-4 py-3 rounded-lg">
                <dl class="grid grid-cols-1 gap-2">
                  <div class="flex justify-between">
                    <dt class="text-gray-600">書類種類</dt>
                    <dd class="font-medium">{{ delivery.delivery_type }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-gray-600">受付日時</dt>
                    <dd class="font-medium">{{ formatDate(delivery.received_at) }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- 電子印付き書類画像 -->
            <div class="mb-8">
              <div class="bg-white rounded-lg shadow overflow-hidden">
                <img
                  :src="sealedDocumentUrl"
                  :alt="delivery.delivery_type"
                  class="w-full h-auto"
                />
              </div>
            </div>

            <!-- 印刷ボタン -->
            <div class="flex justify-center space-x-4">
              <button
                @click="printDocument"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                書類を印刷
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  delivery: {
    type: Object,
    required: true,
  },
  sealedDocumentUrl: {
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

// 書類印刷
const printDocument = () => {
  const printWindow = window.open('', '_blank');
  printWindow.document.write('<!DOCTYPE html>' +
    '<html>' +
      '<head>' +
        '<title>' + props.delivery.delivery_type + '印刷</title>' +
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
          '<h2>' + props.delivery.delivery_type + '</h2>' +
          '<p>受付日時: ' + formatDate(props.delivery.received_at) + '</p>' +
        '</div>' +
        '<img src="' + props.sealedDocumentUrl + '" class="document-image" />' +
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
