<template>
  <ReceptionLayout
    title="処理完了"
    :showBackButton="false"
    :steps="['納品・集荷選択', '情報入力', '完了']"
    :currentStep="2"
  >
    <CompleteSection title="電子印の処理が完了しました">
      <template #description>
        <p>以下のQRコードを印刷して、書類と一緒にお渡しください。</p>
        <p class="text-sm text-gray-500 mt-1">QRコードから電子印付きの書類を確認できます</p>
      </template>

      <!-- QRコード表示 -->
      <div class="max-w-sm mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <!-- QRコード画像を直接表示 -->
          <div class="mb-4 flex justify-center">
            <img 
              :src="qrCodeImageUrl" 
              alt="QRコード" 
              class="w-48 h-48 object-contain"
              @error="handleImageError"
            />
            <!-- エラー時の代替表示 -->
            <div class="w-48 h-48 flex items-center justify-center bg-gray-100 border-2 border-dashed border-gray-300 text-gray-500 text-sm" style="display: none;">
              QRコード画像を読み込めませんでした
            </div>
          </div>
          <div class="text-sm text-gray-600">
            <div class="font-medium">{{ delivery.delivery_type }}</div>
            <div>{{ formatDate(delivery.received_at) }}</div>
          </div>
          <!-- QRコード画像URL -->
          <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500 mb-1">QRコード画像URL:</div>
            <div class="text-xs text-blue-600 break-all">{{ qrCodeImageUrl }}</div>
            <div class="text-xs text-gray-500 mt-2 mb-1">印刷用URL:</div>
            <div class="text-xs text-blue-600 break-all">{{ qrCodePrintUrl }}</div>
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
          :href="route('delivery.create')"
        >
          続けて登録
        </Button>
      </template>
    </CompleteSection>
  </ReceptionLayout>
</template>

<script setup>
import { computed } from 'vue';
import axios from 'axios';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import CompleteSection from '@/Components/UI/CompleteSection.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
  qrCode: {
    type: String,
    required: true,
  },
  delivery: {
    type: Object,
    required: true,
  },
});

// QRコード画像のURLを生成（画像表示用）
const qrCodeImageUrl = computed(() => {
  if (!props.delivery?.id) {
    return '';
  }
  return route('delivery.qr', props.delivery.id);
});

// 印刷用のURL（qr_code_urlを使用）
const qrCodePrintUrl = computed(() => {
  return props.delivery.qr_code_url || '';
});

// 画像読み込みエラーハンドリング
const handleImageError = (event) => {
  console.error('QRコード画像の読み込みに失敗しました:', event);
  // エラー時は代替テキストを表示
  event.target.style.display = 'none';
  event.target.nextElementSibling.style.display = 'block';
};

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

// QRコード印刷（プリントサーバーに送信）
const printQR = async () => {
  try {
    // プリントサーバーに送信（Flask側でURLを受け取って印刷）
    const response = await axios.post('https://192.168.210.91:5000/print', {
      url: qrCodePrintUrl.value, // 印刷用URL（qr_code_url）
    }, {
      headers: { 'Content-Type': 'application/json' },
      timeout: 10000, // 10秒でタイムアウト
    });

    const result = response.data;
    console.log('📨 サーバー応答:', result);

    // Flask側の戻り値 { status: "success" | "error", message?, url?, file? }
    if (result.status === 'success') {
      alert('✅ 印刷が正常に完了しました！\n\nQRコードが印刷されました。');
    } else {
      alert('❌ プリントサーバーへの送信に失敗しました: ' + (result.message || '原因不明'));
    }

  } catch (error) {
    console.error('プリントサーバー送信エラー:', error);

    if (error.code === 'ECONNABORTED') {
      alert('⏳ 接続がタイムアウトしました。プリントサーバーが起動中か確認してください。');
    } else if (error.response) {
      alert(`⚠️ サーバーエラー: ${error.response.status} - ${error.response.statusText}`);
    } else {
      alert('❌ プリントサーバーへの送信中にエラーが発生しました。');
    }
  }
};

</script>