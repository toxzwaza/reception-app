<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          納品書・受領書詳細 (ID: {{ delivery.id }})
        </h2>
        <div class="flex space-x-2">
          <Link
            :href="route('admin.deliveries.index')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            一覧に戻る
          </Link>
          <button
            v-if="!delivery.sealed_at"
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
          <!-- 書類情報 -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">書類情報</h3>
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ delivery.id }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">書類種別</dt>
                  <dd class="mt-1">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        delivery.delivery_type === '納品書'
                          ? 'bg-blue-100 text-blue-800'
                          : 'bg-green-100 text-green-800',
                      ]"
                    >
                      {{ delivery.delivery_type }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">受付日時</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ formatDate(delivery.received_at) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">電子印状態</dt>
                  <dd class="mt-1">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        delivery.sealed_at
                          ? 'bg-green-100 text-green-800'
                          : 'bg-yellow-100 text-yellow-800',
                      ]"
                    >
                      {{ delivery.sealed_at ? "電子印済み" : "未押印" }}
                    </span>
                  </dd>
                </div>
                <div v-if="delivery.sealed_at">
                  <dt class="text-sm font-medium text-gray-500">
                    電子印押下日時
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ formatDate(delivery.sealed_at) }}
                  </dd>
                </div>
                <div v-if="delivery.staff_member_id">
                  <dt class="text-sm font-medium text-gray-500">押印者ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ delivery.staff_member_id }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- QRコード情報 -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">
                QRコード情報
              </h3>
              <div v-if="qrCodeUrl" class="text-center">
                <img
                  :src="qrCodeUrl"
                  alt="QRコード"
                  class="w-48 h-48 object-contain mx-auto mb-4"
                />
                <p class="text-sm text-gray-600 mb-2">
                  QRコードから書類を確認できます
                </p>
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

        <!-- 書類画像（電子印済み画像がある場合はそちらを優先表示） -->
        <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
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
              <div v-if="rotationAngle !== 0" class="mt-2 text-sm text-orange-600">
                回転角度: {{ rotationAngle }}°（プレビュー）
              </div>
              <div class="mt-4 flex justify-center gap-2 flex-wrap" style="position: relative; z-index: 10;">
                <!-- 画像回転ボタン（プレビュー用） -->
                <button
                  @click="previewRotate(90)"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                  title="時計回りに90度回転（プレビュー）"
                >
                  ↻ 90°回転
                </button>
                <button
                  @click="previewRotate(-90)"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                  title="反時計回りに90度回転（プレビュー）"
                >
                  ↺ 90°回転
                </button>
                <button
                  @click="previewRotate(180)"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                  title="180度回転（プレビュー）"
                >
                  ↻ 180°回転
                </button>
                <button
                  v-if="rotationAngle !== 0"
                  @click="resetRotation"
                  class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm"
                  title="回転をリセット"
                >
                  リセット
                </button>
                <button
                  v-if="rotationAngle !== 0"
                  @click="saveRotation"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                  title="回転を保存"
                >
                  保存
                </button>
                <a
                  :href="displayImageUrl"
                  target="_blank"
                  :class="[
                    'text-white font-bold py-2 px-4 rounded',
                    delivery.sealed_document_image
                      ? 'bg-green-500 hover:bg-green-700'
                      : 'bg-blue-500 hover:bg-blue-700',
                  ]"
                >
                  {{
                    delivery.sealed_document_image
                      ? "電子印済み画像を別ウィンドウで開く"
                      : "画像を別ウィンドウで開く"
                  }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 発注データ紐づけ -->
        <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              発注データ紐づけ
            </h3>

            <!-- 紐づけ済み発注データの表示（複数件対応） -->
            <div v-if="linkedOrders && linkedOrders.length > 0" class="mb-6">
              <h4 class="text-md font-medium text-gray-900 mb-4">紐づけ済み発注データ ({{ linkedOrders.length }}件)</h4>
              <div v-for="(linkedOrder, index) in linkedOrders" :key="linkedOrder.id" class="mb-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <span class="text-sm text-gray-600">#{{ index + 1 }}</span>
                    <span class="ml-2 text-xs px-2 py-1 rounded"
                      :class="linkedOrder.pivot_delivery_type === 'complete' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                      {{ linkedOrder.pivot_delivery_type === 'complete' ? '完納' : '分納' }}
                    </span>
                    <span class="ml-2 text-xs px-2 py-1 rounded"
                      :class="linkedOrder.pivot_signage_display === 'show' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'">
                      サイネージ: {{ linkedOrder.pivot_signage_display === 'show' ? '表示あり' : '表示なし' }}
                    </span>
                  </div>
                  <button
                    @click="handleUnlinkOrder(linkedOrder.id)"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm"
                  >
                    紐づけ解除
                  </button>
                  </div>
                <div class="border border-gray-200 rounded-lg overflow-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-3 bg-gray-100">注文No</th>
                        <th class="px-4 py-3 bg-gray-100">画像</th>
                        <th class="px-4 py-3 bg-gray-100">注文者</th>
                        <th class="px-4 py-3 bg-gray-100">注文日</th>
                        <th class="px-4 py-3 bg-gray-100">希望納期</th>
                        <th class="px-4 py-3 bg-gray-100">注文先</th>
                        <th class="px-4 py-3 bg-gray-100">品名</th>
                        <th class="px-4 py-3 bg-gray-100">品番</th>
                        <th class="px-4 py-3 bg-gray-100">数量</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr>
                        <td class="px-4 py-6">{{ linkedOrder.order_no || "-" }}</td>
                        <td class="w-24 px-4 py-6">
                          <img
                            v-if="linkedOrder.img_path"
                            @click="modalImage($event.target)"
                            :src="
                              linkedOrder.img_path &&
                              linkedOrder.img_path.includes('https://')
                                ? linkedOrder.img_path
                                : 'https://akioka.cloud/' + linkedOrder.img_path
                            "
                            alt="商品画像"
                            class="cursor-pointer"
                          />
                          <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-4 py-6">{{ linkedOrder.order_user || "-" }}</td>
                        <td class="px-4 py-6">
                          {{
                            linkedOrder.order_date
                              ? new Date(linkedOrder.order_date).toLocaleDateString(
                                  "ja-JP"
                                )
                              : "-"
                          }}
                        </td>
                        <td class="px-4 py-6">
                          {{
                            linkedOrder.desire_delivery_date
                              ? new Date(
                                  linkedOrder.desire_delivery_date
                                ).toLocaleDateString("ja-JP")
                              : "未指定"
                          }}
                        </td>
                        <td class="px-4 py-6">{{ linkedOrder.com_name || "-" }}</td>
                        <td class="px-4 py-6">{{ linkedOrder.name || "-" }}</td>
                        <td class="px-4 py-6">{{ linkedOrder.s_name || "-" }}</td>
                        <td class="px-4 py-6">
                          {{ linkedOrder.quantity ? linkedOrder.quantity + (linkedOrder.order_unit || "") : "-" }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- 絞り込みブロック（常に表示） -->
            <div class="space-y-4">
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-900 mb-4">絞り込み条件</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <!-- 注文No -->
                  <div>
                    <label
                      for="filter_order_no"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      注文No
                    </label>
                    <input
                      id="filter_order_no"
                      v-model="filters.orderNo"
                      type="text"
                      placeholder="注文Noを入力"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      @keyup.enter="handleSearch"
                    />
                  </div>

                  <!-- 注文者 -->
                  <div>
                    <label
                      for="filter_order_user"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      注文者
                    </label>
                    <select
                      id="filter_order_user"
                      v-model="filters.orderUser"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">すべて</option>
                      <option
                        v-for="user in uniqueOrderUsers"
                        :key="user"
                        :value="user"
                      >
                        {{ user }}
                      </option>
                    </select>
                  </div>

                  <!-- 注文先 -->
                  <div>
                    <label
                      for="filter_com_name"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      注文先
                    </label>
                    <div>
                      <input
                        id="filter_com_name"
                        v-model="filters.comName"
                        list="com_name_list"
                        type="text"
                        placeholder="注文先を入力または選択）"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        @keyup.enter="handleSearch"
                      />
                      <datalist id="com_name_list">
                        <option
                          v-for="company in comNames"
                          :key="company"
                          :value="company"
                        >
                          {{ company }}
                        </option>
                      </datalist>
                    </div>
                  </div>

                  <!-- 品名・品番 -->
                  <div>
                    <label
                      for="filter_product"
                      class="block text-sm font-medium text-gray-700 mb-2"
                    >
                      品名・品番
                    </label>
                    <input
                      id="filter_product"
                      v-model="filters.product"
                      type="text"
                      placeholder="品名・品番を入力"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      @keyup.enter="handleSearch"
                    />
                  </div>
                </div>
                <div class="mt-4 flex justify-end">
                  <button
                    @click="handleSearch"
                    :disabled="isSearching"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:bg-gray-400 disabled:cursor-not-allowed"
                  >
                    {{ isSearching ? "検索中..." : "絞り込み" }}
                  </button>
                </div>
              </div>

              <!-- 検索結果表示 -->
              <div v-if="searchResults.length > 0" class="mt-4">
                <h4 class="text-md font-medium text-gray-900 mb-2">
                  検索結果 ({{ searchResults.length }}件)
                </h4>
                <div class="border border-gray-200 rounded-lg overflow-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-3 bg-gray-100">注文No</th>
                        <th class="px-4 py-3 bg-gray-100">画像</th>
                        <th class="px-4 py-3 bg-gray-100">注文者</th>
                        <th class="px-4 py-3 bg-gray-100">注文日</th>
                        <th class="px-4 py-3 bg-gray-100">希望納期</th>
                        <th class="px-4 py-3 bg-gray-100">注文先</th>
                        <th class="px-4 py-3 bg-gray-100">品名</th>
                        <th class="px-4 py-3 bg-gray-100">品番</th>
                        <th class="px-4 py-3 bg-gray-100">数量</th>
                        <th class="px-4 py-3 bg-gray-100">操作</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="order in searchResults" :key="order.id">
                        <td class="px-4 py-6">{{ order.order_no || "-" }}</td>
                        <td class="w-24 px-4 py-6">
                          <img
                            v-if="order.img_path"
                            @click="modalImage($event.target)"
                            :src="
                              order.img_path &&
                              order.img_path.includes('https://')
                                ? order.img_path
                                : 'https://akioka.cloud/' + order.img_path
                            "
                            alt="商品画像"
                            class="cursor-pointer"
                          />
                          <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-4 py-6">{{ order.order_user || "-" }}</td>
                        <td class="px-4 py-6">
                          {{
                            order.order_date
                              ? new Date(order.order_date).toLocaleDateString(
                                  "ja-JP"
                                )
                              : "-"
                          }}
                        </td>
                        <td class="px-4 py-6">
                          {{
                            order.desire_delivery_date
                              ? new Date(
                                  order.desire_delivery_date
                                ).toLocaleDateString("ja-JP")
                              : "未指定"
                          }}
                        </td>
                        <td class="px-4 py-6">{{ order.com_name || "-" }}</td>
                        <td class="px-4 py-6">{{ order.name || "-" }}</td>
                        <td class="px-4 py-6">{{ order.s_name || "-" }}</td>
                        <td class="px-4 py-6">
                          {{ order.quantity ? order.quantity + (order.order_unit || "") : "-" }}
                        </td>
                        <td class="px-4 py-6">
                          <button
                            @click="linkOrder(order.id)"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs"
                          >
                            紐づける
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- 検索結果なし -->
              <div
                v-if="
                  (filters.orderNo || filters.orderUser || filters.comName || filters.product) &&
                  searchResults.length === 0 &&
                  !isSearching
                "
                class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
              >
                <p class="text-sm text-yellow-800">
                  該当する発注データが見つかりませんでした。
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 電子印配置モーダル -->
    <div
      v-if="showSealOverlay"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-6xl max-h-[90vh] overflow-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">電子印配置</h3>
          <button
            @click="showSealOverlay = false"
            class="text-gray-500 hover:text-gray-700"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>

        <SealOverlay
          :original-image-url="documentUrl"
          @save="handleSealSave"
          @cancel="showSealOverlay = false"
        />
      </div>
    </div>

    <!-- 納品種別選択モーダル -->
    <div
      v-if="showDeliveryTypeModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">納品設定</h3>
          <button
            @click="showDeliveryTypeModal = false; selectedOrderId = null"
            class="text-gray-500 hover:text-gray-700"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>
        <div class="space-y-6">
          <!-- 納品種別選択 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              納品種別
            </label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="deliveryTypeSelection"
                  value="partial"
                  class="mr-2"
                />
                <span class="text-sm text-gray-700">分納</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="deliveryTypeSelection"
                  value="complete"
                  class="mr-2"
                />
                <span class="text-sm text-gray-700">完納</span>
              </label>
            </div>
          </div>

          <!-- サイネージディスプレイ選択 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              サイネージディスプレイ
            </label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="signageDisplaySelection"
                  value="show"
                  class="mr-2"
                />
                <span class="text-sm text-gray-700">表示あり</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="signageDisplaySelection"
                  value="hide"
                  class="mr-2"
                />
                <span class="text-sm text-gray-700">表示なし</span>
              </label>
            </div>
          </div>

          <!-- 確定ボタン -->
          <div class="pt-4 border-t">
            <button
              @click="confirmLinkOrder"
              class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded"
            >
              確定
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import SealOverlay from "@/Components/SealOverlay.vue";

const props = defineProps({
  delivery: Object,
  documentUrl: String,
  qrCodeUrl: String,
  linkedOrders: {
    type: Array,
    default: () => [],
  },
});

// 電子印配置モーダルの表示状態
const showSealOverlay = ref(false);

// 納品種別選択モーダルの表示状態
const showDeliveryTypeModal = ref(false);
const selectedOrderId = ref(null);
const deliveryTypeSelection = ref("complete"); // デフォルト: 完納
const signageDisplaySelection = ref("show"); // デフォルト: サイネージ表示あり

// 画像回転のプレビュー角度（累積）
const rotationAngle = ref(0);

// 発注データ紐づけ関連
const searchResults = ref([]);
const allInitialOrders = ref([]);
const isSearching = ref(false);

// 注文先リスト（APIから取得）
const comNames = ref([]);

// 絞り込み条件
const filters = ref({
  orderNo: "",
  orderUser: "",
  comName: "",
  comNameSelect: "",
  product: "",
});

// ユニークな注文者リスト
const uniqueOrderUsers = computed(() => {
  const users = new Set();
  allInitialOrders.value.forEach((order) => {
    if (order.order_user) {
      users.add(order.order_user);
    }
  });
  return Array.from(users).sort();
});

// 電子印済み書類画像のURL
const sealedDocumentUrl = computed(() => {
  if (!props.delivery.sealed_document_image) {
    return null;
  }
  // 絶対URLの場合はそのまま、相対パスの場合は/storage/を付与
  const imagePath = props.delivery.sealed_document_image;
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath;
  }
  return `/storage/${imagePath}`;
});

// 表示する画像URL（電子印済み画像を優先、なければ元の書類画像）
const displayImageUrl = computed(() => {
  return sealedDocumentUrl.value || props.documentUrl;
});

// 表示する画像タイトル
const displayImageTitle = computed(() => {
  return props.delivery.sealed_document_image
    ? "電子印済み書類画像"
    : "書類画像";
});

// 日付フォーマット（日時）
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString("ja-JP", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// 日付フォーマット（日のみ）
const formatDateOnly = (dateString) => {
  return new Date(dateString).toLocaleDateString("ja-JP", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
};

// 電子印保存処理
const handleSealSave = async (sealPositions) => {
  if (sealPositions.length === 0) {
    alert("電子印を配置してください。");
    return;
  }

  try {
    // Inertia.jsのrouter.postを使用（CSRFトークンが自動で処理される）
    router.post(
      route("admin.deliveries.apply-seal", props.delivery.id),
      {
        seal_positions: sealPositions,
        // staff_member_idは現在のユーザーから自動取得（認証システム実装時に追加）
      },
      {
        onSuccess: (page) => {
          alert("✅ 電子印が正常に適用されました！");
          showSealOverlay.value = false;
        },
        onError: (errors) => {
          console.error("電子印適用エラー:", errors);
          alert(
            "❌ 電子印の適用に失敗しました: " +
              (errors.message || "不明なエラー")
          );
        },
      }
    );
  } catch (error) {
    console.error("電子印適用エラー:", error);
    alert("❌ 電子印の適用中にエラーが発生しました。");
  }
};

// 未納品発注データを取得
const loadInitialOrders = async () => {
  isSearching.value = true;
  try {
    const response = await fetch("/api/initial-orders");
    if (!response.ok) {
      throw new Error("APIリクエストに失敗しました");
    }
    const data = await response.json();
    allInitialOrders.value = data || [];
    searchResults.value = allInitialOrders.value;
  } catch (error) {
    console.error("発注データ取得エラー:", error);
    alert("❌ 発注データの取得に失敗しました。");
    allInitialOrders.value = [];
    searchResults.value = [];
  } finally {
    isSearching.value = false;
  }
};

// 注文先リストを取得
const loadComNames = async () => {
  try {
    const response = await fetch("/api/com-names");
    if (!response.ok) {
      throw new Error("APIリクエストに失敗しました");
    }
    const data = await response.json();
    console.log(data);
    comNames.value = data || [];
  } catch (error) {
    console.error("注文先リスト取得エラー:", error);
    comNames.value = [];
  }
};

// コンポーネントマウント時に注文先リストを取得
onMounted(() => {
  loadComNames();
});

// 絞り込み検索を実行（データ取得 + フィルタリング）
const handleSearch = async () => {
  await loadInitialOrders();
  applyFilters();
};

// 絞り込みを適用
const applyFilters = () => {
  // 既に紐づけられている発注データのIDリストを取得
  const linkedOrderIds = props.linkedOrders.map(order => order.id);
  
  searchResults.value = allInitialOrders.value.filter((order) => {
    // 既に紐づけられている発注データは除外
    if (linkedOrderIds.includes(order.id)) {
      return false;
    }

    // 注文Noで絞り込み
    if (filters.value.orderNo) {
      const orderNo = order.order_no?.toString() || "";
      if (!orderNo.toLowerCase().includes(filters.value.orderNo.toLowerCase())) {
        return false;
      }
    }

    // 注文者で絞り込み
    if (filters.value.orderUser) {
      if (order.order_user !== filters.value.orderUser) {
        return false;
      }
    }

    // 注文先で絞り込み
    if (filters.value.comName) {
      const comName = order.com_name || "";
      if (!comName.toLowerCase().includes(filters.value.comName.toLowerCase())) {
        return false;
      }
    }

    // 品名・品番で絞り込み
    if (filters.value.product) {
      const productQuery = filters.value.product.toLowerCase();
      const name = order.name || "";
      const sName = order.s_name || "";
      const searchableText = `${name} ${sName}`.toLowerCase();
      if (!searchableText.includes(productQuery)) {
        return false;
      }
    }

    return true;
  });
};

// 画像モーダル表示
const modalImage = (imgElement) => {
  const imgSrc = imgElement.src;
  const modal = document.createElement("div");
  modal.className =
    "fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 cursor-pointer";
  modal.onclick = () => document.body.removeChild(modal);

  const img = document.createElement("img");
  img.src = imgSrc;
  img.className = "max-w-4xl max-h-[90vh] object-contain";
  img.onclick = (e) => e.stopPropagation();

  modal.appendChild(img);
  document.body.appendChild(modal);
};

// 発注データを紐づける（モーダルを表示）
const linkOrder = (orderId) => {
  selectedOrderId.value = orderId;
  // デフォルト値をリセット
  deliveryTypeSelection.value = "complete";
  signageDisplaySelection.value = "show";
  showDeliveryTypeModal.value = true;
};

// 納品種別を選択して紐づけを実行
const confirmLinkOrder = async () => {
  if (!selectedOrderId.value) {
    return;
  }

  try {
    router.post(
      route("admin.deliveries.link-order", props.delivery.id),
      {
        order_id: selectedOrderId.value,
        delivery_type: deliveryTypeSelection.value, // 'partial' or 'complete'
        signage_display: signageDisplaySelection.value, // 'show' or 'hide'
      },
      {
        onSuccess: (page) => {
          alert("✅ 発注データを紐づけました！");
          showDeliveryTypeModal.value = false;
          selectedOrderId.value = null;
          // 絞り込み条件をリセット
          filters.value = {
            orderNo: "",
            orderUser: "",
            comName: "",
            comNameSelect: "",
            product: "",
          };
          searchResults.value = [];
          // ページをリロード
          router.visit(route("admin.deliveries.show", props.delivery.id), {
            method: "get",
            preserveState: false,
            preserveScroll: false,
            only: ["delivery", "documentUrl", "qrCodeUrl", "linkedOrders"],
          });
        },
        onError: (errors) => {
          console.error("発注データ紐づけエラー:", errors);
          alert(
            "❌ 発注データの紐づけに失敗しました: " +
              (errors.message || "不明なエラー")
          );
        },
      }
    );
  } catch (error) {
    console.error("発注データ紐づけエラー:", error);
    alert("❌ 発注データの紐づけ中にエラーが発生しました。");
  }
};

// 発注データの紐づけを解除
const handleUnlinkOrder = async (orderId) => {
  if (!confirm("発注データの紐づけを解除しますか？")) {
    return;
  }

  try {
    router.post(
      route("admin.deliveries.unlink-order", props.delivery.id),
      {
        order_id: orderId,
      },
      {
        onSuccess: (page) => {
          alert("✅ 発注データの紐づけを解除しました！");
          // ページをリロード
          router.visit(route("admin.deliveries.show", props.delivery.id), {
            method: "get",
            preserveState: false,
            preserveScroll: false,
            only: ["delivery", "documentUrl", "qrCodeUrl", "linkedOrders"],
          });
        },
        onError: (errors) => {
          console.error("発注データ紐づけ解除エラー:", errors);
          alert(
            "❌ 発注データの紐づけ解除に失敗しました: " +
              (errors.message || "不明なエラー")
          );
        },
      }
    );
  } catch (error) {
    console.error("発注データ紐づけ解除エラー:", error);
    alert("❌ 発注データの紐づけ解除中にエラーが発生しました。");
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
      route("admin.deliveries.rotate-image", props.delivery.id),
      {
        angle: rotationAngle.value,
      },
      {
        onSuccess: (page) => {
          alert("✅ 画像を回転して保存しました！");
          rotationAngle.value = 0; // リセット
          // ページをリロードして画像を再読み込み
          router.visit(route("admin.deliveries.show", props.delivery.id), {
            method: "get",
            preserveState: false,
            preserveScroll: false,
            only: ["delivery", "documentUrl", "qrCodeUrl", "linkedOrder"],
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
