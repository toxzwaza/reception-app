<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          集荷伝票詳細 (ID: {{ pickup.id }})
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('admin.pickups.index')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            一覧に戻る
          </Link>
          <button 
            v-if="!pickup.sealed_at"
            @click="showSealOverlay = true"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
          >
            電子印押下
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- 伝票情報 -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">伝票情報</h3>
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ pickup.id }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">書類種別</dt>
                  <dd class="mt-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                      集荷伝票
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">集荷日時</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(pickup.picked_up_at) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">電子印状態</dt>
                  <dd class="mt-1">
                    <span :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      pickup.sealed_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ]">
                      {{ pickup.sealed_at ? '電子印済み' : '未押印' }}
                    </span>
                  </dd>
                </div>
                <div v-if="pickup.sealed_at">
                  <dt class="text-sm font-medium text-gray-500">電子印押下日時</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(pickup.sealed_at) }}</dd>
                </div>
                <div v-if="pickup.staff_member_id">
                  <dt class="text-sm font-medium text-gray-500">押印者ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ pickup.staff_member_id }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- QRコード情報 -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">QRコード情報</h3>
              <div v-if="qrCodeUrl" class="text-center">
                <img 
                  :src="qrCodeUrl" 
                  alt="QRコード" 
                  class="w-48 h-48 object-contain mx-auto mb-4"
                />
                <p class="text-sm text-gray-600 mb-2">QRコードから伝票を確認できます</p>
                <a 
                  :href="qrCodeUrl" 
                  target="_blank"
                  class="text-blue-600 hover:text-blue-800 text-sm"
                >
                  QRコードを別ウィンドウで開く
                </a>
              </div>
              <div v-else class="text-center text-gray-500">
                <p>QRコードが生成されていません</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 伝票画像 -->
        <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">伝票画像</h3>
            <div class="text-center">
              <img 
                :src="slipUrl" 
                alt="伝票画像" 
                class="max-w-full h-auto mx-auto rounded-lg shadow-lg"
                style="max-height: 600px;"
              />
              <div class="mt-4">
                <a 
                  :href="slipUrl" 
                  target="_blank"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                  画像を別ウィンドウで開く
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 電子印済み伝票画像 -->
        <div v-if="pickup.sealed_slip_image" class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">電子印済み伝票画像</h3>
            <div class="text-center">
              <img 
                :src="sealedSlipUrl" 
                alt="電子印済み伝票画像" 
                class="max-w-full h-auto mx-auto rounded-lg shadow-lg"
                style="max-height: 600px;"
              />
              <div class="mt-4">
                <a 
                  :href="sealedSlipUrl" 
                  target="_blank"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                  電子印済み画像を別ウィンドウで開く
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 電子印配置モーダル -->
    <div v-if="showSealOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-6xl max-h-[90vh] overflow-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">電子印配置</h3>
          <button @click="showSealOverlay = false" class="text-gray-500 hover:text-gray-700">
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

const props = defineProps({
  pickup: Object,
  slipUrl: String,
  qrCodeUrl: String,
});

// 電子印配置モーダルの表示状態
const showSealOverlay = ref(false);

// 電子印済み伝票画像のURL
const sealedSlipUrl = computed(() => {
  return props.pickup.sealed_slip_image 
    ? `/storage/${props.pickup.sealed_slip_image}` 
    : null;
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
</script>
