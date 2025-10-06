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
            <div class="font-medium">集荷伝票</div>
            <div>{{ formatDate(pickup.picked_up_at) }}</div>
          </div>
          <!-- QRコード画像URL -->
          <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500 mb-1">QRコード画像URL:</div>
            <div class="text-xs text-blue-600 break-all">{{ qrCodeImageUrl }}</div>
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
import { computed } from 'vue';
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

// QRコード画像のURLを生成
const qrCodeImageUrl = computed(() => {
  return route('pickup.qr', props.pickup.id);
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
    // プリントサーバーに送信（画像データはサーバー側で処理）
    const response = await fetch(route('pickup.print', props.pickup.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        document_info: {
          document_type: '集荷伝票',
          timestamp: props.pickup.picked_up_at,
          id: props.pickup.id
        }
      })
    });

    const result = await response.json();

    if (result.success) {
      // 印刷完了ステータスをチェック
      if (result.status === 'completed') {
        // 印刷完了ダイアログ
        alert('✅ 印刷が正常に完了しました！\n\nQRコードが印刷されました。伝票と一緒にお渡しください。');
      } else {
        // 送信完了ダイアログ
        alert('📤 プリントサーバーに正常に送信されました。\n\n印刷処理中です。しばらくお待ちください。');
      }
    } else {
      alert('❌ プリントサーバーへの送信に失敗しました: ' + result.message);
    }
  } catch (error) {
    console.error('プリントサーバー送信エラー:', error);
    alert('プリントサーバーへの送信中にエラーが発生しました。');
  }
};
</script>