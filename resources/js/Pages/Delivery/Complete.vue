<template>
  <ReceptionLayout
    title="処理完了"
    :showBackButton="false"
    :steps="['納品・集荷選択', '情報入力', '完了']"
    :currentStep="2"
  >
    <div class="mx-auto max-w-2xl">
      <div class="rounded-2xl border border-white/60 bg-white/85 px-8 py-10 text-center shadow-xl shadow-slate-900/5 backdrop-blur-md sm:px-12">
        <!-- 完了アイコン -->
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-green-600 shadow-lg shadow-green-600/30">
          <svg class="h-11 w-11 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <!-- 案内メッセージ -->
        <div class="mx-auto mt-8 max-w-lg rounded-xl border-2 border-amber-300 bg-amber-50 px-6 py-5">
          <p class="text-lg font-bold leading-relaxed text-amber-800">
            受領印が必要な場合は、QRコードを印刷ボタンを押下して、受領印としてご利用ください。
          </p>
        </div>

        <!-- 印刷ボタン -->
        <div class="mx-auto mt-6 max-w-sm">
          <Button
            variant="success"
            size="lg"
            fullWidth
            @click="printQR"
          >
            <template #icon-left>
              <svg class="mr-2.5 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
            </template>
            QRコードを印刷
          </Button>
          <p class="mt-3 text-sm text-slate-400">QRコードから電子印付きの書類を確認できます</p>
        </div>

        <!-- 区切り線 -->
        <div class="mx-auto my-8 max-w-lg border-t border-slate-200"></div>

        <!-- アクションボタン -->
        <div class="flex justify-center gap-4">
          <Button
            variant="outline"
            :href="route('home')"
            class="min-w-[160px]"
          >
            トップに戻る
          </Button>
          <Button
            variant="primary"
            :href="route('delivery.create')"
            class="min-w-[160px]"
          >
            続けて登録
          </Button>
        </div>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { computed } from 'vue';
import axios from 'axios';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
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

// 印刷用のURL（qr_code_urlを使用）
const qrCodePrintUrl = computed(() => {
  return props.delivery.qr_code_url || '';
});

// QRコード印刷（プリントサーバーに送信）
const printQR = async () => {
  const printUrl = qrCodePrintUrl.value;
  
  if (!printUrl) {
    alert('❌ 印刷用URLが設定されていません。');
    return;
  }
  
  console.log('🖨️ 印刷リクエスト開始');
  console.log('印刷用URL:', printUrl);
  
  try {
    // プリントサーバーに送信（Flask側でURLを受け取って印刷）
    const response = await axios.post('https://192.168.210.90:5000/print', {
      url: printUrl, // 印刷用URL（qr_code_url）
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
    console.error('エラー詳細:', {
      message: error.message,
      code: error.code,
      response: error.response,
      request: error.request,
    });

    if (error.code === 'ECONNABORTED') {
      alert('⏳ 接続がタイムアウトしました。プリントサーバーが起動中か確認してください。');
    } else if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
      alert('❌ ネットワークエラー\n\n考えられる原因:\n• プリントサーバー(192.168.210.90:5000)が起動していない\n• ファイアウォールでブロックされている\n• CORS設定の問題\n• SSL証明書の問題');
    } else if (error.response) {
      alert(`⚠️ サーバーエラー: ${error.response.status} - ${error.response.statusText}`);
    } else if (error.request) {
      alert('❌ サーバーからの応答がありません。プリントサーバーの状態を確認してください。');
    } else {
      alert('❌ プリントサーバーへの送信中にエラーが発生しました。\n\nエラー: ' + error.message);
    }
  }
};

</script>