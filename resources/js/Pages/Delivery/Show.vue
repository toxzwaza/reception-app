<template>
  <!-- お客様用の閲覧専用ページ。受付システム等へ遷移できないよう、ナビ/リンクは一切置かない -->
  <div class="min-h-screen bg-gray-100 py-8 px-4">
    <div class="max-w-3xl mx-auto">
      <!-- 見出し＋受付日時 -->
      <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">
        {{ delivery.delivery_type }}
      </h1>
      <p class="text-center text-gray-600 mb-6">
        受付日時：{{ formatDate(delivery.received_at) }}
      </p>

      <!-- 納品書（電子印付き）画像 -->
      <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <img
          :src="sealedDocumentUrl"
          :alt="delivery.delivery_type"
          class="w-full h-auto"
        />
      </div>

      <!-- 印刷ボタン -->
      <div class="flex justify-center">
        <button
          @click="printDocument"
          class="px-8 py-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-lg shadow-lg"
        >
          印刷する
        </button>
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

// 書類印刷（別ウィンドウで納品書のみ印刷）
const printDocument = () => {
  const printWindow = window.open('', '_blank');
  printWindow.document.write('<!DOCTYPE html>' +
    '<html>' +
      '<head>' +
        '<title>' + props.delivery.delivery_type + '印刷</title>' +
        '<style>' +
          'body { margin: 0; padding: 20px; }' +
          '.document-info { margin-bottom: 20px; font-family: sans-serif; }' +
          '.document-image { width: 100%; height: auto; }' +
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
