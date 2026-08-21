<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          納品書・受領書管理
        </h2>
        <div class="flex gap-2">
          <button
            @click="openCameraModal"
            class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
          >
            ＋ 納品書追加
          </button>
          <Link
            :href="route('admin.dashboard')"
            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ← ダッシュボード
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 検索フィルター -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6">
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">書類種別</label>
              <select v-model="filters.delivery_type" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">すべて</option>
                <option value="納品書">納品書</option>
                <option value="受領書">受領書</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">電子印状態</label>
              <select v-model="filters.seal_status" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">すべて</option>
                <option value="sealed">電子印済み</option>
                <option value="unsealed">未押印</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">開始日</label>
              <input
                type="date"
                v-model="filters.date_from"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">終了日</label>
              <input
                type="date"
                v-model="filters.date_to"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
            </div>
            <div class="flex items-end gap-2">
              <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
              >
                検索
              </button>
              <button
                type="button"
                @click="clearFilters"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
              >
                クリア
              </button>
            </div>
          </form>
        </div>

        <!-- 納品書・受領書一覧 -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <button @click="sortBy('id')" class="inline-flex items-center gap-1 hover:text-slate-700">
                      ID
                      <span v-if="filters.sort_by === 'id'">{{ filters.sort_order === 'asc' ? '↑' : '↓' }}</span>
                    </button>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">画像</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">書類種別</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <button @click="sortBy('received_at')" class="inline-flex items-center gap-1 hover:text-slate-700">
                      受付日時
                      <span v-if="filters.sort_by === 'received_at'">{{ filters.sort_order === 'asc' ? '↑' : '↓' }}</span>
                    </button>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">品名・品番</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">電子印状態</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <!-- 行クリックで詳細へ遷移（絞り込み条件を引き継ぐ） -->
                <tr
                  v-for="delivery in deliveries.data"
                  :key="delivery.id"
                  @click="openDetail(delivery.id)"
                  class="hover:bg-blue-50/50 transition-colors cursor-pointer"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                    {{ delivery.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <img
                      v-if="thumbnailUrl(delivery)"
                      :src="thumbnailUrl(delivery)"
                      loading="lazy"
                      alt="納品書サムネイル"
                      @click.stop="previewImageUrl = thumbnailUrl(delivery)"
                      class="h-12 w-16 object-cover rounded-md border border-slate-200 hover:ring-2 hover:ring-blue-400 transition"
                    >
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="delivery.delivery_type === '納品書' ? 'info' : 'success'">
                      {{ delivery.delivery_type }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ formatDate(delivery.received_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    <template v-if="delivery.initial_orders && delivery.initial_orders.length > 0">
                      {{ delivery.initial_orders[0].name || '-' }} / {{ delivery.initial_orders[0].s_name || '-' }}
                      <span v-if="delivery.initial_orders_count > 1" class="ml-1 text-xs font-medium text-slate-500">
                        他{{ delivery.initial_orders_count - 1 }}件
                      </span>
                    </template>
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="delivery.sealed_document_image ? 'success' : 'warning'" dot>
                      {{ delivery.sealed_document_image ? '電子印済み' : '未押印' }}
                    </Badge>
                  </td>
                </tr>
                <tr v-if="deliveries.data.length === 0">
                  <td colspan="6" class="px-6 py-10 text-center text-slate-400">該当する書類がありません。</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ページネーション -->
          <div class="flex justify-between items-center px-6 py-4 border-t border-slate-200">
            <div class="text-sm text-slate-600">
              {{ deliveries.from }} - {{ deliveries.to }} / 全 {{ deliveries.total }} 件
            </div>
            <div class="flex space-x-2">
              <Link
                v-for="link in deliveries.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm leading-tight border rounded-lg transition',
                  link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-500 border-slate-300 hover:bg-slate-50'
                ]"
              >
                <span v-html="link.label"></span>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- サムネイル拡大モーダル -->
    <div
      v-if="previewImageUrl"
      @click="previewImageUrl = null"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
    >
      <button
        @click="previewImageUrl = null"
        class="absolute top-4 right-4 text-white text-3xl leading-none hover:text-slate-300"
        aria-label="閉じる"
      >
        ×
      </button>
      <img
        :src="previewImageUrl"
        alt="納品書画像"
        @click.stop
        class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl bg-white"
      >
    </div>

    <!-- 納品書追加（カメラ撮影）モーダル -->
    <div
      v-if="showCameraModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      role="dialog"
      aria-modal="true"
      aria-label="納品書追加"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden">
        <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-200">
          <h3 class="text-lg font-semibold text-slate-800">納品書追加（カメラ撮影）</h3>
          <button @click="closeCameraModal" :disabled="submitting" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">×</button>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto p-6 space-y-4">
          <!-- カメラ選択（複数台対応・前回使用したカメラを自動選択） -->
          <div v-if="!capturedUrl" class="flex items-end gap-3">
            <div class="flex-1">
              <label class="block text-sm font-medium text-slate-700 mb-1">使用するカメラ</label>
              <select
                v-model="selectedDeviceId"
                @change="switchCamera"
                :disabled="videoDevices.length <= 1"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option v-for="d in videoDevices" :key="d.deviceId" :value="d.deviceId">
                  {{ d.label || `カメラ (${d.deviceId.slice(0, 8)}…)` }}
                </option>
              </select>
            </div>
            <div class="w-44">
              <label class="block text-sm font-medium text-slate-700 mb-1">書類種別</label>
              <select v-model="newDeliveryType" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="納品書">納品書</option>
                <option value="その他書類">その他書類</option>
              </select>
            </div>
          </div>

          <!-- ライブプレビュー／撮影確認 -->
          <div class="relative bg-slate-900 rounded-xl overflow-hidden flex items-center justify-center min-h-[300px]">
            <video
              v-show="!capturedUrl"
              ref="videoEl"
              autoplay
              playsinline
              muted
              class="w-full max-h-[50vh] object-contain"
            ></video>
            <img
              v-if="capturedUrl"
              :src="capturedUrl"
              alt="撮影画像プレビュー"
              class="w-full max-h-[50vh] object-contain"
            >
            <div v-if="cameraError" class="absolute inset-0 flex items-center justify-center bg-slate-100 p-6">
              <p class="text-sm text-red-600 text-center">{{ cameraError }}</p>
            </div>
          </div>

          <p v-if="capturedUrl" class="text-sm text-slate-500 text-center">
            この画像で登録しますか？（種別：{{ newDeliveryType }}）
          </p>
        </div>

        <div class="flex-shrink-0 flex gap-3 px-6 py-4 border-t border-slate-200">
          <button
            @click="closeCameraModal"
            :disabled="submitting"
            class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg font-semibold transition disabled:opacity-50"
          >
            キャンセル
          </button>
          <template v-if="!capturedUrl">
            <button
              @click="captureImage"
              :disabled="!!cameraError"
              class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              撮影
            </button>
          </template>
          <template v-else>
            <button
              @click="retake"
              :disabled="submitting"
              class="flex-1 py-3 bg-slate-500 hover:bg-slate-600 text-white rounded-lg font-semibold transition disabled:opacity-50"
            >
              撮り直す
            </button>
            <button
              @click="submitCapture"
              :disabled="submitting"
              class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition disabled:opacity-50"
            >
              {{ submitting ? '登録中…' : '登録する' }}
            </button>
          </template>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
  deliveries: Object,
  filters: Object,
});

const filters = reactive({
  delivery_type: props.filters.delivery_type || '',
  seal_status: props.filters.seal_status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  sort_by: props.filters.sort_by || 'received_at',
  sort_order: props.filters.sort_order || 'desc',
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

// 値の入っている絞り込み条件だけを抽出（詳細画面への引き継ぎ用）
const activeFilters = () => {
  const out = {};
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== '' && value !== null) out[key] = value;
  });
  return out;
};

// 行クリック：絞り込み条件を引き継いで詳細へ（詳細側の前へ/次へが同じ並びで動く）
const openDetail = (id) => {
  router.get(route('admin.deliveries.show', id), activeFilters());
};

// サムネイル（電子印済み画像を優先＝詳細画面と同じ考え方）
const thumbnailUrl = (delivery) => delivery.sealed_document_image || delivery.document_image || null;

// サムネイル拡大モーダル
const previewImageUrl = ref(null);

// フィルター適用
const applyFilters = () => {
  router.get(route('admin.deliveries.index'), filters, {
    preserveState: true,
    preserveScroll: true,
  });
};

// フィルタークリア
const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    if (key === 'sort_by') {
      filters[key] = 'received_at';
    } else if (key === 'sort_order') {
      filters[key] = 'desc';
    } else {
      filters[key] = '';
    }
  });
  applyFilters();
};

// ソート
const sortBy = (column) => {
  if (filters.sort_by === column) {
    filters.sort_order = filters.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    filters.sort_by = column;
    filters.sort_order = 'asc';
  }
  applyFilters();
};

/* ===== 納品書追加（カメラ撮影） ===== */

// 前回使用したカメラを記憶するlocalStorageキー
const CAMERA_STORAGE_KEY = 'admin_delivery_camera_id';

const showCameraModal = ref(false);
const videoEl = ref(null);
const videoDevices = ref([]);
const selectedDeviceId = ref('');
const cameraError = ref('');
const capturedBlob = ref(null);
const capturedUrl = ref(null);
const newDeliveryType = ref('納品書');
const submitting = ref(false);
let mediaStream = null;

const stopStream = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach((t) => t.stop());
    mediaStream = null;
  }
};

// 指定deviceId（省略時は既定カメラ）でカメラを起動する
const startCamera = async (deviceId) => {
  stopStream();
  cameraError.value = '';
  const constraints = {
    video: deviceId
      ? { deviceId: { exact: deviceId }, width: { ideal: 1920 }, height: { ideal: 1080 } }
      : { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
    audio: false,
  };
  try {
    mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
  } catch (e) {
    if (deviceId) {
      // 保存済みカメラが無効（外付けカメラの取り外し等）→ 既定カメラへフォールバック
      localStorage.removeItem(CAMERA_STORAGE_KEY);
      return startCamera(null);
    }
    cameraError.value = `カメラを起動できませんでした（${e.name}）。カメラの接続とブラウザの権限を確認してください。`;
    return;
  }

  if (videoEl.value) videoEl.value.srcObject = mediaStream;

  // 実際に使用中のカメラを特定して記憶（次回自動選択）
  const track = mediaStream.getVideoTracks()[0];
  const usedId = track && track.getSettings().deviceId;
  if (usedId) {
    selectedDeviceId.value = usedId;
    localStorage.setItem(CAMERA_STORAGE_KEY, usedId);
  }

  // カメラ一覧を更新（ラベルは権限許可後でないと取得できないためこの順序で行う）
  const devices = await navigator.mediaDevices.enumerateDevices();
  videoDevices.value = devices.filter((d) => d.kind === 'videoinput');
};

const openCameraModal = () => {
  showCameraModal.value = true;
  capturedBlob.value = null;
  capturedUrl.value = null;
  startCamera(localStorage.getItem(CAMERA_STORAGE_KEY) || null);
};

const switchCamera = () => {
  localStorage.setItem(CAMERA_STORAGE_KEY, selectedDeviceId.value);
  startCamera(selectedDeviceId.value);
};

const captureImage = () => {
  const video = videoEl.value;
  if (!video || !video.videoWidth) {
    cameraError.value = 'カメラ映像の準備中です。少し待ってから撮影してください。';
    return;
  }
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);
  canvas.toBlob((blob) => {
    capturedBlob.value = blob;
    capturedUrl.value = URL.createObjectURL(blob);
  }, 'image/jpeg', 0.92);
};

const retake = () => {
  if (capturedUrl.value) URL.revokeObjectURL(capturedUrl.value);
  capturedBlob.value = null;
  capturedUrl.value = null;
};

const submitCapture = () => {
  if (!capturedBlob.value || submitting.value) return;
  submitting.value = true;
  router.post(
    route('admin.deliveries.store'),
    {
      delivery_type: newDeliveryType.value,
      document_image: new File([capturedBlob.value], 'document.jpg', { type: 'image/jpeg' }),
    },
    {
      forceFormData: true,
      onError: (errors) => {
        submitting.value = false;
        alert('登録に失敗しました: ' + Object.values(errors).join('\n'));
      },
      onFinish: () => {
        // 成功時はサーバー側リダイレクトで詳細画面へ遷移する
        stopStream();
      },
    }
  );
};

const closeCameraModal = () => {
  stopStream();
  retake();
  cameraError.value = '';
  showCameraModal.value = false;
};

onUnmounted(stopStream); // 画面遷移時のカメラ占有解放
</script>
