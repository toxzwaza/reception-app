<template>
  <ReceptionLayout
    title="処理完了"
    :showBackButton="false"
    :steps="['情報入力', '伝票撮影', '完了']"
    :currentStep="2"
  >
    <CompleteSection title="電子印の処理が完了しました">
      <template #description>
        <p>以下のQRコードを印刷して、伝票と一緒にお渡しください。</p>
        <p class="text-sm text-gray-500 mt-1">QRコードから電子印付きの伝票を確認できます</p>
      </template>

      <!-- QRコード表示 -->
      <div class="max-w-sm mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="mb-4" v-html="qrCode"></div>
          <div class="text-sm text-gray-600">
            <div class="font-medium">{{ pickup.company_name }}</div>
            <div>集荷伝票</div>
            <div>{{ formatDate(pickup.picked_up_at) }}</div>
          </div>
        </div>

        <!-- 印刷ボタン -->
        <div class="mt-6">
          <Button
            variant="primary"
            @click="printQR"
            class="w-full"
          >
            <template #icon-left>
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
            </template>
            QRコードを印刷
          </Button>
        </div>
      </div>

      <!-- アクションボタン -->
      <template #actions>
        <Button
          variant="outline"
          :href="route('home')"
        >
          トップに戻る
        </Button>
        <Button
          variant="primary"
          :href="route('pickup.create')"
        >
          続けて登録
        </Button>
      </template>
    </CompleteSection>
  </ReceptionLayout>
</template>

<script setup>
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import CompleteSection from '@/Components/UI/CompleteSection.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
  qrCode: {
    type: String,
    required: true,
  },
  pickup: {
    type: Object,
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

// QRコード印刷
const printQR = () => {
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <html>
      <head>
        <title>QRコード印刷</title>
        <style>
          body {
            margin: 0;
            padding: 20px;
            font-family: sans-serif;
          }
          .container {
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
          }
          .qr-code {
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
          }
          .info {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
          }
          .company {
            font-weight: bold;
            font-size: 16px;
            color: #333;
          }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="qr-code">
            ${props.qrCode}
          </div>
          <div class="info">
            <div class="company">${props.pickup.company_name}</div>
            <div>集荷伝票</div>
            <div>${formatDate(props.pickup.picked_up_at)}</div>
          </div>
        </div>
        <script>
          window.onload = () => {
            window.print();
            window.close();
          };
        </script>
      </body>
    </html>
  `);
  printWindow.document.close();
};
</script>