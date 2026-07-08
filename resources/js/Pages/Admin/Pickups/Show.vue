<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          集荷伝票詳細 (ID: {{ pickup.id }})
        </h2>
        <div class="flex gap-2">
          <Link
            :href="route('admin.pickups.index')"
            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ← 一覧に戻る
          </Link>
          <button
            v-if="!pickup.sealed_at"
            @click="showSealOverlay = true"
            class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
          >
            電子印押下
          </button>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- 伝票情報 -->
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-slate-800 mb-4">伝票情報</h3>
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-slate-500">ID</dt>
                  <dd class="mt-1 text-sm text-slate-800">{{ pickup.id }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">書類種別</dt>
                  <dd class="mt-1">
                    <Badge variant="purple">集荷伝票</Badge>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">集荷日時</dt>
                  <dd class="mt-1 text-sm text-slate-800">{{ formatDate(pickup.picked_up_at) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">電子印状態</dt>
                  <dd class="mt-1">
                    <Badge :variant="pickup.sealed_at ? 'success' : 'warning'" dot>
                      {{ pickup.sealed_at ? '電子印済み' : '未押印' }}
                    </Badge>
                  </dd>
                </div>
                <div v-if="pickup.sealed_at">
                  <dt class="text-sm font-medium text-slate-500">電子印押下日時</dt>
                  <dd class="mt-1 text-sm text-slate-800">{{ formatDate(pickup.sealed_at) }}</dd>
                </div>
                <div v-if="pickup.staff_member_id">
                  <dt class="text-sm font-medium text-slate-500">押印者ID</dt>
                  <dd class="mt-1 text-sm text-slate-800">{{ pickup.staff_member_id }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- QRコード情報 -->
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-slate-800 mb-4">QRコード情報</h3>
              <div v-if="qrCodeUrl" class="text-center">
                <img
                  :src="qrCodeUrl"
                  alt="QRコード"
                  class="w-48 h-48 object-contain mx-auto mb-4"
                />
                <p class="text-sm text-slate-600 mb-2">QRコードから伝票を確認できます</p>
                <a
                  :href="qrCodeUrl"
                  target="_blank"
                  class="text-blue-600 hover:text-blue-800 text-sm"
                >
                  QRコードを別ウィンドウで開く
                </a>
              </div>
              <div v-else class="text-center text-slate-500">
                <p>QRコードが生成されていません</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 伝票画像（電子印済み画像がある場合はそちらを優先表示） -->
        <div class="mt-8 bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">
              {{ displayImageTitle }}
            </h3>
            <div class="text-center">
              <!-- プレビュー表示用の画像コンテナ（回転を考慮したサイズ） -->
              <div
                class="inline-block mx-auto"
                :style="{
                  width: (rotationAngle === 90 || rotationAngle === 270) ? '800px' : 'auto',
                  height: (rotationAngle === 90 || rotationAngle === 270) ? 'auto' : '600px',
                  maxWidth: '100%',
                  maxHeight: (rotationAngle === 90 || rotationAngle === 270) ? '800px' : '600px',
                  minHeight: '200px',
                  minWidth: (rotationAngle === 90 || rotationAngle === 270) ? '600px' : 'auto',
                  overflow: 'hidden',
                  position: 'relative',
                  padding: (rotationAngle === 90 || rotationAngle === 270) ? '50px 0' : '0',
                  boxSizing: 'border-box',
                }"
              >
                <div
                  class="mx-auto rounded-lg shadow-lg transition-transform duration-300"
                  :style="{
                    transform: `rotate(${rotationAngle}deg)`,
                    transformOrigin: 'center center',
                    display: 'inline-block',
                    verticalAlign: 'middle',
                  }"
                >
                  <img
                    :src="displayImageUrl"
                    :alt="displayImageTitle"
                    class="max-w-full h-auto rounded-lg"
                    style="max-height: 600px; max-width: 600px; display: block;"
                  />
                </div>
              </div>
              <div v-if="rotationAngle !== 0" class="mt-2 text-sm text-amber-600">
                回転角度: {{ rotationAngle }}°（プレビュー）
              </div>
              <div class="mt-4 flex justify-center gap-2 flex-wrap" style="position: relative; z-index: 10;">
                <!-- 画像回転ボタン（プレビュー用） -->
                <button
                  @click="previewRotate(90)"
                  class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                  title="時計回りに90度回転（プレビュー）"
                >
                  ↻ 90°回転
                </button>
                <button
                  @click="previewRotate(-90)"
                  class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                  title="反時計回りに90度回転（プレビュー）"
                >
                  ↺ 90°回転
                </button>
                <button
                  @click="previewRotate(180)"
                  class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                  title="180度回転（プレビュー）"
                >
                  ↻ 180°回転
                </button>
                <button
                  v-if="rotationAngle !== 0"
                  @click="resetRotation"
                  class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                  title="回転をリセット"
                >
                  リセット
                </button>
                <button
                  v-if="rotationAngle !== 0"
                  @click="saveRotation"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                  title="回転を保存"
                >
                  保存
                </button>
                <a
                  :href="displayImageUrl"
                  target="_blank"
                  :class="[
                    'text-white font-semibold py-2 px-4 rounded-lg transition',
                    pickup.sealed_slip_image
                      ? 'bg-emerald-600 hover:bg-emerald-700'
                      : 'bg-blue-600 hover:bg-blue-700',
                  ]"
                >
                  {{
                    pickup.sealed_slip_image
                      ? "電子印済み画像を別ウィンドウで開く"
                      : "画像を別ウィンドウで開く"
                  }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 電子印配置モーダル -->
    <div v-if="showSealOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-6xl max-h-[90vh] overflow-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-slate-800">電子印配置</h3>
          <button @click="showSealOverlay = false" class="text-slate-500 hover:text-slate-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <SealOverlay
          :original-image-url="slipUrl"
          @save="handleSealSave"
          @cancel="showSealOverlay = false"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SealOverlay from '@/Components/SealOverlay.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
  pickup: Object,
  slipUrl: String,
  qrCodeUrl: String,
});

// 電子印配置モーダルの表示状態
const showSealOverlay = ref(false);

// 画像回転のプレビュー角度（累積）
const rotationAngle = ref(0);

// 電子印済み伝票画像のURL
const sealedSlipUrl = computed(() => {
  if (!props.pickup.sealed_slip_image) {
    return null;
  }
  // 絶対URLの場合はそのまま、相対パスの場合は/storage/を付与
  const imagePath = props.pickup.sealed_slip_image;
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath;
  }
  return `/storage/${imagePath}`;
});

// 表示する画像URL（電子印済み画像を優先、なければ元の伝票画像）
const displayImageUrl = computed(() => {
  return sealedSlipUrl.value || props.slipUrl;
});

// 表示する画像タイトル
const displayImageTitle = computed(() => {
  return props.pickup.sealed_slip_image
    ? "電子印済み伝票画像"
    : "伝票画像";
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

// 電子印保存処理
const handleSealSave = async (sealPositions) => {
  if (sealPositions.length === 0) {
    alert('電子印を配置してください。');
    return;
  }

  try {
    // Inertia.jsのrouter.postを使用（CSRFトークンが自動で処理される）
    router.post(route('admin.pickups.apply-seal', props.pickup.id), {
      seal_positions: sealPositions
      // staff_member_idは現在のユーザーから自動取得（認証システム実装時に追加）
    }, {
      onSuccess: (page) => {
        alert('✅ 電子印が正常に適用されました！');
        showSealOverlay.value = false;
      },
      onError: (errors) => {
        console.error('電子印適用エラー:', errors);
        alert('❌ 電子印の適用に失敗しました: ' + (errors.message || '不明なエラー'));
      }
    });
  } catch (error) {
    console.error('電子印適用エラー:', error);
    alert('❌ 電子印の適用中にエラーが発生しました。');
  }
};

// 画像を回転（プレビュー）
const previewRotate = (angle) => {
  rotationAngle.value = (rotationAngle.value + angle) % 360;
  // 負の角度を正の角度に変換（例: -90 → 270）
  if (rotationAngle.value < 0) {
    rotationAngle.value += 360;
  }
};

// 回転をリセット
const resetRotation = () => {
  rotationAngle.value = 0;
};

// 画像回転を保存
const saveRotation = async () => {
  if (rotationAngle.value === 0) {
    return;
  }

  if (!confirm(`画像を${rotationAngle.value}度回転して保存しますか？`)) {
    return;
  }

  try {
    router.post(
      route("admin.pickups.rotate-image", props.pickup.id),
      {
        angle: rotationAngle.value,
      },
      {
        onSuccess: (page) => {
          alert("✅ 画像を回転して保存しました！");
          rotationAngle.value = 0; // リセット
          // ページをリロードして画像を再読み込み
          router.visit(route("admin.pickups.show", props.pickup.id), {
            method: "get",
            preserveState: false,
            preserveScroll: false,
            only: ["pickup", "slipUrl", "qrCodeUrl"],
          });
        },
        onError: (errors) => {
          console.error("画像回転エラー:", errors);
          alert(
            "❌ 画像の回転に失敗しました: " +
              (errors.message || "不明なエラー")
          );
        },
      }
    );
  } catch (error) {
    console.error("画像回転エラー:", error);
    alert("❌ 画像の回転中にエラーが発生しました。");
  }
};
</script>
