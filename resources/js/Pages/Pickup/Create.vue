<template>
  <ReceptionLayout
    title="集荷業者受付"
    subtitle="受領書をスキャンしてください"
  >
    <div class="p-8">
      <form @submit.prevent="submitForm">
        <div class="max-w-4xl mx-auto">
          <!-- スキャン開始画面（スキャン中・プレビュー確認はモーダルで表示） -->
          <div v-if="!showCamera && !form.slip_preview" class="text-center py-12">
            <div class="w-32 h-32 mx-auto mb-6 text-orange-500">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">受領書をスキャンしてください</h2>
            <p class="text-gray-600 mb-8">スキャンボタンを押してスキャンを開始してください</p>
            
            <button
              type="button"
              @click="startCamera"
              class="px-12 py-6 bg-indigo-600 text-white text-xl rounded-lg font-semibold hover:bg-indigo-700 shadow-lg"
            >
              スキャンを開始
            </button>
          </div>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="cameraError" class="mt-4 text-center text-sm text-red-600" role="alert">
          {{ cameraError }}
        </div>

        <!-- ====== スキャンモーダル ====== -->
        <!-- スキャン開始（showCamera）からスキャン完了プレビュー（slip_preview）まで
             一連の流れを全画面モーダルで表示。1画面で完結しスクロール不要。 -->
        <div
          v-if="showCamera || form.slip_preview"
          class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
          role="dialog"
          aria-modal="true"
          :aria-label="form.slip_preview ? 'スキャン画像の確認' : 'スキャン中'"
        >
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden">
            <!-- ヘッダー -->
            <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200">
              <h3 v-if="!form.slip_preview" class="text-xl font-semibold text-gray-900 text-center">受領書をスキャン中...</h3>
              <h3 v-else class="text-xl font-semibold text-gray-900 text-center">こちらの画像でよろしいですか？</h3>
            </div>

            <!-- ========== スキャン中（ライブ映像） ========== -->
            <template v-if="!form.slip_preview">
              <!-- ライブ映像エリア -->
              <div class="flex-1 min-h-0 flex items-center justify-center bg-gray-900 p-4 overflow-hidden">
                <div class="relative max-h-full">
                  <!-- ビデオフィード表示 -->
                  <img
                    :src="videoFeedUrl"
                    alt="スキャン映像"
                    class="max-h-[60vh] max-w-full object-contain rounded-lg"
                    @error="handleVideoFeedError"
                  />
                  <!-- ビデオフィードエラー時のフォールバック表示 -->
                  <div v-if="videoFeedError" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded-lg">
                    <div class="text-center">
                      <div class="mb-4">
                        <svg class="animate-spin h-16 w-16 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </div>
                      <div class="text-2xl font-semibold text-gray-700">スキャン中...</div>
                      <div class="text-sm text-gray-500 mt-2">書類をスキャナーにセットしてください</div>
                    </div>
                  </div>
                  <!-- スキャン枠オーバーレイ -->
                  <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="border-4 border-indigo-500 border-dashed rounded-xl opacity-50" style="width: 85%; height: 85%;"></div>
                  </div>
                </div>
              </div>

              <!-- 固定フッター（ヒント・キャンセル） -->
              <div class="flex-shrink-0 border-t border-gray-200 p-4 space-y-3">
                <!-- ヒント（折りたたみ） -->
                <details class="bg-blue-50 rounded-lg px-4 py-2 text-sm">
                  <summary class="cursor-pointer select-none font-medium text-blue-800 flex items-center gap-2">
                    <svg class="h-4 w-4 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    スキャンのヒント（タップで開く）
                  </summary>
                  <ul class="mt-2 text-blue-700 list-disc pl-5 space-y-1">
                    <li>書類をスキャナーに正しくセットしてください</li>
                    <li>書類が曲がっていないか確認してください</li>
                    <li>スキャン完了までお待ちください</li>
                  </ul>
                </details>

                <!-- 検出状態バナー -->
                <div
                  class="rounded-lg px-4 py-2 text-sm font-medium text-center"
                  :class="documentDetected ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'"
                >
                  <span v-if="documentDetected">書類を認識しました（赤枠）。「スキャン」で撮影してください。</span>
                  <span v-else>書類を認識していません。位置を調整するか「手動撮影」で撮影してください。</span>
                </div>

                <!-- 操作ボタン -->
                <div class="flex gap-3">
                  <button
                    type="button"
                    @click="handleCancel"
                    :disabled="isCapturing"
                    class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 disabled:opacity-50 text-lg"
                  >
                    キャンセル
                  </button>
                  <button
                    type="button"
                    @click="captureManual"
                    :disabled="isCapturing"
                    class="flex-1 py-4 bg-gray-500 text-white rounded-lg font-semibold hover:bg-gray-600 disabled:opacity-50 text-lg"
                  >
                    手動撮影
                  </button>
                  <button
                    type="button"
                    @click="captureScan"
                    :disabled="!documentDetected || isCapturing"
                    class="flex-1 py-4 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
                  >
                    {{ isCapturing ? '撮影中...' : 'スキャン' }}
                  </button>
                </div>
              </div>
            </template>

            <!-- ========== スキャン完了プレビュー（確認） ========== -->
            <template v-else>
            <!-- 画像プレビュー（中央配置・はみ出し時は縮小） -->
            <div class="flex-1 min-h-0 flex items-center justify-center bg-gray-100 p-4 overflow-hidden">
              <img
                :src="form.slip_preview"
                alt="受領書"
                class="max-h-full max-w-full object-contain rounded-lg shadow transition-transform duration-200"
                :style="{ transform: `rotate(${rotationDegrees}deg)` }"
              />
            </div>

            <!-- 固定フッター（回転・注意文・操作ボタン） -->
            <div class="flex-shrink-0 border-t border-gray-200 p-4 space-y-3">
              <!-- 回転コントロール -->
              <div class="flex gap-2">
                <button
                  type="button"
                  @click="rotateLeft"
                  :disabled="processing"
                  class="flex-1 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 disabled:opacity-50 text-base flex items-center justify-center gap-2"
                  aria-label="左に90度回転"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                  </svg>
                  左に90°
                </button>
                <button
                  type="button"
                  @click="rotate180"
                  :disabled="processing"
                  class="flex-1 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 disabled:opacity-50 text-base"
                  aria-label="180度回転"
                >
                  180°
                </button>
                <button
                  type="button"
                  @click="rotateRight"
                  :disabled="processing"
                  class="flex-1 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 disabled:opacity-50 text-base flex items-center justify-center gap-2"
                  aria-label="右に90度回転"
                >
                  右に90°
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 000 12h3" />
                  </svg>
                </button>
              </div>

              <!-- 注意文（折りたたみ。既定は閉じてスクロール不要に） -->
              <details class="bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm">
                <summary class="cursor-pointer select-none font-medium text-red-800 flex items-center gap-2">
                  <svg class="h-4 w-4 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  撮影内容の確認（タップで開く）
                </summary>
                <ul class="mt-2 text-red-700 list-disc pl-5 space-y-1">
                  <li>テキストが可読か</li>
                  <li>手や指で隠れていないか</li>
                  <li>書類全体が写っているか</li>
                  <li>明るさが適切か</li>
                  <li>向きが正しいか（回転で調整）</li>
                </ul>
              </details>

              <!-- 操作ボタン -->
              <div class="flex gap-4">
                <button
                  type="button"
                  @click="retakeImage"
                  :disabled="processing"
                  class="flex-1 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 disabled:opacity-50 text-lg"
                >
                  撮り直す
                </button>
                <button
                  type="submit"
                  :disabled="processing"
                  class="flex-1 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-lg"
                >
                  {{ processing ? '処理中...' : '確定' }}
                </button>
              </div>
            </div>
            </template>
          </div>
        </div>
      </form>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, onUnmounted, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { io } from 'socket.io-client';
import axios from 'axios';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

const props = defineProps({
  errors: {
    type: Object,
    default: () => ({}),
  },
  // 集荷依頼から来た場合の依頼ID（依頼一覧で選択された集荷依頼）
  pickupRequestId: {
    type: [Number, String],
    default: null,
  },
});

// ====== スキャンツール設定 ======
// 環境変数から設定を取得（VITE_プレフィックスが必要）
// または、windowオブジェクトから取得（Laravelの環境変数から設定）
const RASPI_IP = import.meta.env.VITE_SCAN_TOOL_IP || window.SCAN_TOOL_IP || "192.168.210.90";
const PORT = import.meta.env.VITE_SCAN_TOOL_PORT || window.SCAN_TOOL_PORT || 5001;

// プロトコルを環境に応じて切り替え
// 開発環境: http, 本番環境: https
// VITE_SCAN_TOOL_PROTOCOL または SCAN_TOOL_PROTOCOL が設定されていない場合は、
// APP_ENV が 'local' または 'development' の場合は http、それ以外は https
const getProtocol = () => {
  // 明示的にプロトコルが指定されている場合
  const explicitProtocol = import.meta.env.VITE_SCAN_TOOL_PROTOCOL || window.SCAN_TOOL_PROTOCOL;
  if (explicitProtocol) {
    return explicitProtocol;
  }
  
  // 環境に応じて自動判定
  const appEnv = import.meta.env.MODE || window.APP_ENV || 'production';
  if (appEnv === 'development' || appEnv === 'local') {
    return 'http';
  }
  return 'https';
};

const PROTOCOL = getProtocol();
const START_URL = `${PROTOCOL}://${RASPI_IP}:${PORT}/start_scan`;
const STOP_URL = `${PROTOCOL}://${RASPI_IP}:${PORT}/stop_scan`;
const CAPTURE_URL = `${PROTOCOL}://${RASPI_IP}:${PORT}/capture`;
const CAPTURE_MANUAL_URL = `${PROTOCOL}://${RASPI_IP}:${PORT}/capture_manual`;
const GET_IMAGE_URL = `${PROTOCOL}://${RASPI_IP}:${PORT}/get_scan_image`;
const VIDEO_FEED_URL = `${PROTOCOL}://${RASPI_IP}:${PORT}/video_feed`;
const WS_URL = `${PROTOCOL}://${RASPI_IP}:${PORT}`;

console.log('📡 スキャンツール設定:', {
  protocol: PROTOCOL,
  ip: RASPI_IP,
  port: PORT,
  startUrl: START_URL,
  videoFeedUrl: VIDEO_FEED_URL,
  wsUrl: WS_URL,
});

// フォームデータ
const form = ref({
  slip_image: null,
  slip_preview: null,
});

// 状態管理
const processing = ref(false);
const showCamera = ref(false);
const cameraError = ref('');
const isScanning = ref(false);  // スキャン中フラグ
const videoFeedError = ref(false);  // ビデオフィードエラーフラグ
const documentDetected = ref(false);  // スキャナー側で書類（赤枠）を検出中か
const isCapturing = ref(false);  // 撮影リクエスト送信中フラグ（二重押下防止）
const videoFeedUrl = ref(VIDEO_FEED_URL);  // ビデオフィードURL
const rotationDegrees = ref(0);  // スキャン画像の回転角度（0/90/180/270）
let socket = null;  // Socket.IO接続

onMounted(() => {
  // Socket.IO接続を初期化
  initializeSocket();
});

onUnmounted(() => {
  disconnectSocket();
});

// Socket.IO接続の初期化
const initializeSocket = () => {
  try {
    // 既存の接続があれば切断
    if (socket) {
      socket.disconnect();
      socket = null;
    }
    
    console.log('🔌 Socket.IO に接続します...', WS_URL);
    console.log('使用プロトコル:', PROTOCOL);
    
    socket = io(WS_URL, {
      // pollingを最初に試行し、その後websocketにアップグレード
      // これにより、WebSocketが失敗してもpollingで接続できる
      transports: ['polling', 'websocket'],
      // 接続タイムアウト（ミリ秒）
      timeout: 15000,
      // 再接続の試行回数
      reconnection: true,
      reconnectionAttempts: 5,
      reconnectionDelay: 1000,
      // 自動再接続の最大遅延
      reconnectionDelayMax: 5000,
    });

    // 接続成功
    socket.on('connect', () => {
      console.log('🔗 Socket.IO 接続成功');
      console.log('接続ID:', socket.id);
      console.log('使用中のトランスポート:', socket.io.engine.transport.name);
      cameraError.value = '';
    });

    // 接続失敗
    socket.on('connect_error', (error) => {
      console.error('❌ Socket.IO接続エラー:', error);
      console.error('エラー詳細:', {
        message: error.message,
        type: error.type,
        description: error.description,
        data: error.data,
      });
      
      // エラーの種類に応じてメッセージを変更
      let errorMessage = 'スキャンツールへの接続に失敗しました。';
      let detailedMessage = '';
      
      if (error.message.includes('certificate') || error.message.includes('SSL') || error.message.includes('TLS')) {
        errorMessage = 'SSL証明書の検証に失敗しました。';
        detailedMessage = 'サーバーの証明書を確認してください。';
      } else if (error.message.includes('timeout') || error.message.includes('timed out')) {
        errorMessage = '接続がタイムアウトしました。';
        detailedMessage = 'サーバーが起動しているか、ネットワーク接続を確認してください。';
      } else if (error.message.includes('ECONNREFUSED') || error.message.includes('refused') || error.message.includes('Failed to fetch')) {
        errorMessage = '接続が拒否されました。';
        detailedMessage = `サーバー(${RASPI_IP}:${PORT})が起動しているか、ポートが正しいか確認してください。`;
      } else if (error.message.includes('network') || error.message.includes('Network') || error.message.includes('ERR_NETWORK')) {
        errorMessage = 'ネットワークエラーが発生しました。';
        detailedMessage = 'サーバーに到達できるか、ファイアウォール設定を確認してください。';
      } else if (error.message.includes('websocket error') || error.type === 'TransportError') {
        errorMessage = 'WebSocket接続に失敗しました。';
        detailedMessage = 'サーバーがSocket.IOをサポートしているか、CORS設定を確認してください。Pollingに自動的にフォールバックします。';
      } else {
        detailedMessage = `エラー: ${error.message || '不明なエラー'}`;
      }
      
      cameraError.value = `${errorMessage} ${detailedMessage}`;
    });

    // 切断
    socket.on('disconnect', (reason) => {
      console.log('❌ Socket.IO 切断:', reason);
      if (reason === 'io server disconnect') {
        // サーバー側で切断された場合、手動で再接続
        console.log('サーバー側で切断されました。再接続を試みます...');
        socket.connect();
      }
    });

    // 再接続試行
    socket.on('reconnect_attempt', (attemptNumber) => {
      console.log(`🔄 再接続試行中... (${attemptNumber}回目)`);
      cameraError.value = `再接続を試みています... (${attemptNumber}回目)`;
    });

    // 再接続成功
    socket.on('reconnect', (attemptNumber) => {
      console.log(`✅ 再接続成功 (${attemptNumber}回目の試行)`);
      console.log('使用中のトランスポート:', socket.io.engine.transport.name);
      cameraError.value = '';
    });

    // 再接続エラー
    socket.on('reconnect_error', (error) => {
      console.error('再接続エラー:', error);
    });

    // 再接続失敗
    socket.on('reconnect_failed', () => {
      console.error('❌ 再接続に失敗しました。サーバーに接続できません。');
      cameraError.value = `スキャンツール(${RASPI_IP}:${PORT})への接続に失敗しました。サーバーの状態を確認してください。`;
    });

    // スキャン完了イベント
    socket.on('scan_completed', async (data) => {
      console.log('📩 スキャン完了通知を受信:', data);
      isCapturing.value = false;
      documentDetected.value = false;
      await handleScanCompleted();
    });

    // 書類検出状態（赤枠の有無）の通知
    socket.on('detection_state', (data) => {
      documentDetected.value = !!(data && data.detected);
    });

    // トランスポートの変更を監視
    socket.io.on('upgrade', () => {
      console.log('⬆️ トランスポートがアップグレードされました:', socket.io.engine.transport.name);
    });

    socket.io.on('upgradeError', (error) => {
      console.warn('⚠️ トランスポートのアップグレードに失敗しました。Pollingを継続使用します:', error);
    });
  } catch (error) {
    console.error('Socket.IO初期化エラー:', error);
    cameraError.value = `スキャンツールの初期化に失敗しました: ${error.message}`;
  }
};

// Socket.IO接続の切断
const disconnectSocket = () => {
  if (socket) {
    socket.disconnect();
    socket = null;
  }
};

// スキャン開始（外部スキャンツールを使用）
const startCamera = async () => {
  try {
    // Socket.IO接続を確認
    if (!socket || !socket.connected) {
      console.log('Socket.IO接続を再初期化します...');
      initializeSocket();
      
      // 接続を待つ（最大10秒）
      await new Promise((resolve, reject) => {
        const timeout = setTimeout(() => {
          reject(new Error('接続タイムアウト: 10秒以内に接続できませんでした'));
        }, 10000);
        
        const cleanup = () => {
          clearTimeout(timeout);
          socket.off('connect', onConnect);
          socket.off('connect_error', onError);
        };
        
        const onConnect = () => {
          cleanup();
          console.log('✅ Socket.IO接続が確立されました');
          resolve();
        };
        
        const onError = (error) => {
          cleanup();
          console.error('❌ Socket.IO接続エラー:', error);
          reject(error);
        };
        
        socket.once('connect', onConnect);
        socket.once('connect_error', onError);
      });
    } else {
      console.log('✅ Socket.IO接続済み:', socket.id);
    }
    
    console.log('▶ /start_scan を送信してスキャンを開始');
    isScanning.value = true;
    cameraError.value = '';
    videoFeedError.value = false;  // ビデオフィードエラーをリセット
    documentDetected.value = false;  // 検出状態をリセット（detection_stateで更新される）
    isCapturing.value = false;

    // /start_scan にPOSTリクエストを送信
    // ブラウザ環境ではSSL検証の無効化はできないため、サーバー側でCORSとSSL証明書の設定が必要
    const response = await axios.post(START_URL, {}, {
      timeout: 10000,
      validateStatus: (status) => status < 500, // 500未満のステータスコードを許可
    });
    
    console.log('レスポンス:', response.data);
    showCamera.value = true;
    
    // スキャン完了通知を待機（scan_completedイベントで処理）
  } catch (error) {
    console.error('スキャン開始エラー:', error);
    isScanning.value = false;
    showCamera.value = false;
    
    if (error.message && error.message.includes('タイムアウト')) {
      cameraError.value = 'スキャンツールへの接続がタイムアウトしました。サーバーが起動しているか確認してください。';
    } else if (error.code === 'ECONNABORTED') {
      cameraError.value = 'リクエストがタイムアウトしました。';
    } else if (error.response) {
      cameraError.value = `スキャン開始に失敗しました: ${error.response.status} - ${error.response.statusText}`;
    } else if (error.message) {
      cameraError.value = `スキャン開始に失敗しました: ${error.message}`;
    } else {
      cameraError.value = 'スキャン開始に失敗しました。サーバーの状態を確認してください。';
    }
  }
};

// スキャン完了時の処理
const handleScanCompleted = async () => {
  try {
    console.log('📡 /get_scan_image を取得中...');
    
    // /get_scan_image から画像を取得
    // ブラウザ環境ではSSL検証の無効化はできないため、サーバー側でCORSとSSL証明書の設定が必要
    const response = await axios.get(GET_IMAGE_URL, {
      timeout: 10000,
      validateStatus: (status) => status < 500, // 500未満のステータスコードを許可
    });
    
    if (response.status !== 200) {
      throw new Error(`画像取得エラー: ${response.status}`);
    }
    
    const jsonData = response.data;
    const imgBase64 = jsonData.image;
    
    if (!imgBase64) {
      throw new Error('画像データが取得できませんでした');
    }
    
    console.log('💾 画像データを取得しました');
    
    // Base64データをData URLに変換
    const dataUrl = `data:image/jpeg;base64,${imgBase64}`;
    
    // プレビューに表示
    form.value.slip_preview = dataUrl;
    
    // タイムスタンプ付きファイル名を生成
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
    const filename = `slip_${timestamp}.jpg`;
    
    // Base64をFileオブジェクトに変換
    form.value.slip_image = base64ToFile(imgBase64, filename);
    
    console.log('✅ 画像の処理が完了しました:', filename);
    
    // スキャン完了
    isScanning.value = false;
    showCamera.value = false;
    
  } catch (error) {
    console.error('画像取得エラー:', error);
    isScanning.value = false;
    cameraError.value = `画像の取得に失敗しました: ${error.message}`;
  }
};

// Base64文字列をFileオブジェクトに変換
// 「スキャン」ボタン：検出済み書類を確定（未検出時はサーバー側で生フレームにフォールバック）
const captureScan = async () => {
  await sendCapture(CAPTURE_URL, 'スキャン');
};

// 「手動撮影」ボタン：書類認識に頼らずカメラ映像をそのまま撮影
const captureManual = async () => {
  await sendCapture(CAPTURE_MANUAL_URL, '手動撮影');
};

// 撮影リクエスト送信（成功時はサーバーが scan_completed を emit → handleScanCompleted で取得）
const sendCapture = async (url, label) => {
  if (isCapturing.value) return;
  isCapturing.value = true;
  cameraError.value = '';
  try {
    console.log(`▶ ${label}: ${url} を送信`);
    const response = await axios.post(url, {}, {
      timeout: 10000,
      validateStatus: (status) => status < 500,
    });
    if (response.status !== 200 || (response.data && response.data.status === 'error')) {
      throw new Error(response.data?.message || `${label}に失敗しました (${response.status})`);
    }
    // 完了通知（scan_completed）を待機。万一来ない場合に備えフォールバック取得。
    setTimeout(async () => {
      if (isCapturing.value && !form.value.slip_preview) {
        console.warn('scan_completed 未受信 → 画像を直接取得します');
        await handleScanCompleted();
        isCapturing.value = false;
      }
    }, 2500);
  } catch (error) {
    console.error(`${label}エラー:`, error);
    isCapturing.value = false;
    cameraError.value = `${label}に失敗しました: ${error.message}`;
  }
};

const base64ToFile = (base64String, filename) => {
  try {
    // Base64文字列をバイナリに変換
    const binaryString = atob(base64String);
    const bytes = new Uint8Array(binaryString.length);
    
    for (let i = 0; i < binaryString.length; i++) {
      bytes[i] = binaryString.charCodeAt(i);
    }
    
    return new File([bytes], filename, { type: 'image/jpeg' });
  } catch (error) {
    console.error('ファイル変換エラー:', error);
    throw new Error(`画像の処理に失敗しました: ${error.message}`);
  }
};

// ビデオフィードエラーハンドリング
const handleVideoFeedError = (event) => {
  console.error('ビデオフィードの読み込みに失敗しました:', event);
  videoFeedError.value = true;
};

// キャンセル
const handleCancel = () => {
  isScanning.value = false;
  isCapturing.value = false;
  documentDetected.value = false;
  videoFeedError.value = false;  // ビデオフィードエラーをリセット
  showCamera.value = false;
  // スキャナーを待機モードへ戻す（失敗しても致命的ではないため握りつぶす）
  axios.post(STOP_URL, {}, { timeout: 5000, validateStatus: () => true }).catch(() => {});
};

// 撮り直し
const retakeImage = () => {
  form.value.slip_preview = null;
  form.value.slip_image = null;
  rotationDegrees.value = 0;  // 回転もリセット
  isScanning.value = false;
  startCamera();
};

// ====== 回転操作 ======
const rotateLeft = () => {
  rotationDegrees.value = (rotationDegrees.value + 270) % 360;
};
const rotateRight = () => {
  rotationDegrees.value = (rotationDegrees.value + 90) % 360;
};
const rotate180 = () => {
  rotationDegrees.value = (rotationDegrees.value + 180) % 360;
};

// Base64 画像を canvas 経由で指定角度回転させて File に戻す
const rotateImageFile = async (srcDataUrl, degrees, filename) => {
  if (!degrees || degrees === 0) {
    return form.value.slip_image;  // 回転不要
  }
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => {
      try {
        const rad = (degrees * Math.PI) / 180;
        const swap = degrees === 90 || degrees === 270;
        const canvas = document.createElement('canvas');
        canvas.width = swap ? img.height : img.width;
        canvas.height = swap ? img.width : img.height;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.rotate(rad);
        ctx.drawImage(img, -img.width / 2, -img.height / 2);
        canvas.toBlob((blob) => {
          if (!blob) {
            reject(new Error('canvas.toBlob が失敗'));
            return;
          }
          resolve(new File([blob], filename, { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.95);
      } catch (e) {
        reject(e);
      }
    };
    img.onerror = () => reject(new Error('画像読み込み失敗'));
    img.src = srcDataUrl;
  });
};

// フォーム送信
const submitForm = async () => {
  if (!form.value.slip_image) {
    cameraError.value = '受領書をスキャンしてください';
    return;
  }

  processing.value = true;

  try {
    // 回転が指定されていれば実画像を回転してから送信
    let imageToSend = form.value.slip_image;
    if (rotationDegrees.value !== 0 && form.value.slip_preview) {
      imageToSend = await rotateImageFile(
        form.value.slip_preview,
        rotationDegrees.value,
        form.value.slip_image.name || 'slip.jpg'
      );
      console.log(`🔄 画像を ${rotationDegrees.value}° 回転して送信`);
    }

    const formData = {
      slip_image: imageToSend,
      pickup_request_id: props.pickupRequestId || '',
    };

    router.post(route('pickup.store'), formData, {
      forceFormData: true,
      onSuccess: () => {
        processing.value = false;
      },
      onError: (errors) => {
        processing.value = false;
        console.error('送信エラー:', errors);
      },
    });
  } catch (e) {
    processing.value = false;
    cameraError.value = `画像の回転処理に失敗しました: ${e.message}`;
    console.error(e);
  }
};

</script>