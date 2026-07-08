<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          納品書・受領書詳細 (ID: {{ delivery.id }})
        </h2>
        <div class="flex gap-2">
          <Link
            :href="route('admin.deliveries.index')"
            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ← 一覧に戻る
          </Link>
          <button
            v-if="!delivery.sealed_at"
            @click="showSealOverlay = true"
            class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
          >
            電子印押下
          </button>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- 書類情報 -->
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-slate-800 mb-4">書類情報</h3>
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-slate-500">ID</dt>
                  <dd class="mt-1 text-sm text-slate-800">{{ delivery.id }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">書類種別</dt>
                  <dd class="mt-1">
                    <Badge :variant="delivery.delivery_type === '納品書' ? 'info' : 'success'">
                      {{ delivery.delivery_type }}
                    </Badge>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">受付日時</dt>
                  <dd class="mt-1 text-sm text-slate-800">
                    {{ formatDate(delivery.received_at) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">電子印状態</dt>
                  <dd class="mt-1">
                    <Badge :variant="delivery.sealed_at ? 'success' : 'warning'" dot>
                      {{ delivery.sealed_at ? "電子印済み" : "未押印" }}
                    </Badge>
                  </dd>
                </div>
                <div v-if="delivery.sealed_at">
                  <dt class="text-sm font-medium text-slate-500">
                    電子印押下日時
                  </dt>
                  <dd class="mt-1 text-sm text-slate-800">
                    {{ formatDate(delivery.sealed_at) }}
                  </dd>
                </div>
                <div v-if="delivery.staff_member_id">
                  <dt class="text-sm font-medium text-slate-500">押印者ID</dt>
                  <dd class="mt-1 text-sm text-slate-800">
                    {{ delivery.staff_member_id }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- QRコード情報 -->
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-slate-800 mb-4">
                QRコード情報
              </h3>
              <div v-if="qrCodeUrl" class="text-center">
                <img
                  :src="qrCodeUrl"
                  alt="QRコード"
                  class="w-48 h-48 object-contain mx-auto mb-4"
                />
                <p class="text-sm text-slate-600 mb-2">
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
              <div v-else class="text-center text-slate-500">
                <p>QRコードが生成されていません</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 発注紐づけ作業エリア：左=紐づけ操作 / 右=書類画像(sticky) を横並び -->
        <div class="mt-8 flex flex-col-reverse lg:flex-row lg:items-start gap-6">

          <!-- 書類画像（電子印済み画像を優先）：右カラム・スクロール追従(sticky) -->
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm w-full lg:w-[560px] lg:flex-shrink-0 lg:order-2 lg:sticky lg:top-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">
              {{ displayImageTitle }}
            </h3>
            <div class="text-center">
              <!-- ズーム＋ドラッグ移動できる画像ビューア（ダブルクリックで拡大モーダル） -->
              <div
                class="relative mx-auto overflow-hidden rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center"
                :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
                style="height: 58vh;"
                @mousedown.prevent="startDrag"
                @mousemove="onDrag"
                @mouseup="endDrag"
                @mouseleave="endDrag"
                @dblclick="openImageModal"
                title="ドラッグで移動 / ダブルクリックで拡大表示"
              >
                <img
                  :src="displayImageUrl"
                  :alt="displayImageTitle"
                  class="rounded shadow-lg select-none"
                  :style="{
                    transform: `translate(${panX}px, ${panY}px) rotate(${rotationAngle}deg) scale(${imageScale})`,
                    transformOrigin: 'center center',
                    maxWidth: '100%',
                    maxHeight: '54vh',
                    transition: isDragging ? 'none' : 'transform 0.15s ease',
                  }"
                  draggable="false"
                />
              </div>
              <div class="mt-2 flex items-center justify-center gap-x-2 gap-y-1 text-sm text-slate-500 flex-wrap">
                <span>拡大率 {{ Math.round(imageScale * 100) }}%</span>
                <span v-if="rotationAngle !== 0" class="text-amber-600">/ 回転 {{ rotationAngle }}°</span>
                <span class="text-slate-400">｜ドラッグで移動・ダブルクリックで拡大表示</span>
              </div>
              <div class="mt-4 flex justify-center gap-2 flex-wrap" style="position: relative; z-index: 10;">
                <!-- ズームボタン -->
                <button
                  @click="zoomIn"
                  class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="拡大"
                >
                  ＋ 拡大
                </button>
                <button
                  @click="zoomOut"
                  class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="縮小"
                >
                  － 縮小
                </button>
                <button
                  v-if="imageScale !== 1 || panX !== 0 || panY !== 0"
                  @click="resetZoom"
                  class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="拡大率・位置を元に戻す"
                >
                  等倍
                </button>
                <button
                  @click="openImageModal"
                  class="bg-slate-700 hover:bg-slate-900 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="中央に大きく表示して確認"
                >
                  🔍 拡大表示
                </button>
                <!-- 画像回転ボタン（プレビュー用） -->
                <button
                  @click="previewRotate(90)"
                  class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="時計回りに90度回転（プレビュー）"
                >
                  ↻ 90°回転
                </button>
                <button
                  @click="previewRotate(-90)"
                  class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="反時計回りに90度回転（プレビュー）"
                >
                  ↺ 90°回転
                </button>
                <button
                  @click="previewRotate(180)"
                  class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="180度回転（プレビュー）"
                >
                  ↻ 180°回転
                </button>
                <button
                  v-if="rotationAngle !== 0"
                  @click="resetRotation"
                  class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg text-sm"
                  title="回転をリセット"
                >
                  リセット
                </button>
                <button
                  v-if="rotationAngle !== 0"
                  @click="saveRotation"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm"
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
                      ? 'bg-emerald-600 hover:bg-emerald-700'
                      : 'bg-blue-600 hover:bg-blue-700',
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

          <!-- 発注データ紐づけ：左カラム（広め） -->
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm w-full lg:flex-1 min-w-0 lg:order-1">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">
              発注データ紐づけ
            </h3>

            <!-- 紐づけ済み発注データの表示（複数件対応） -->
            <div v-if="linkedOrders && linkedOrders.length > 0" class="mb-6">
              <h4 class="text-md font-medium text-slate-800 mb-4">紐づけ済み発注データ ({{ linkedOrders.length }}件)</h4>
              <div v-for="(linkedOrder, index) in linkedOrders" :key="linkedOrder.id" class="mb-4 p-4 bg-slate-50 rounded-lg">
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <span class="text-sm text-slate-600">#{{ index + 1 }}</span>
                    <span class="ml-2 text-xs font-semibold px-2 py-1 rounded-full"
                      :class="linkedOrder.pivot_delivery_type === 'complete' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                      {{ linkedOrder.pivot_delivery_type === 'complete' ? '完納' : '分納' }}
                    </span>
                    <span class="ml-2 text-xs font-semibold px-2 py-1 rounded-full"
                      :class="linkedOrder.pivot_signage_display === 'show' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'">
                      サイネージ: {{ linkedOrder.pivot_signage_display === 'show' ? '表示あり' : '表示なし' }}
                    </span>
                  </div>
                  <button
                    @click="handleUnlinkOrder(linkedOrder.id)"
                    class="bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                  >
                    紐づけ解除
                  </button>
                  </div>
                <div class="border border-slate-200 rounded-lg overflow-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-50">
                      <tr>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文No</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">画像</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文者</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文日</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">希望納期</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">納品日</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文先</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">品名</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">品番</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">数量</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">単価</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">金額</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr>
                        <td class="px-4 py-6 whitespace-nowrap">{{ linkedOrder.order_no || "-" }}</td>
                        <td class="w-24 px-4 py-6 whitespace-nowrap">
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
                          <span v-else class="text-slate-400">-</span>
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ linkedOrder.order_user || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{
                            linkedOrder.order_date
                              ? new Date(linkedOrder.order_date).toLocaleDateString(
                                  "ja-JP"
                                )
                              : "-"
                          }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{
                            linkedOrder.desire_delivery_date
                              ? new Date(
                                  linkedOrder.desire_delivery_date
                                ).toLocaleDateString("ja-JP")
                              : "未指定"
                          }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{
                            linkedOrder.delivery_date
                              ? new Date(
                                  linkedOrder.delivery_date
                                ).toLocaleDateString("ja-JP")
                              : "-"
                          }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ linkedOrder.com_name || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ linkedOrder.name || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ linkedOrder.s_name || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{ linkedOrder.quantity ? linkedOrder.quantity + (linkedOrder.order_unit || "") : "-" }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{ linkedOrder.price + "円" || "-" }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{ linkedOrder.calc_price + "円" || "-" }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- 絞り込みブロック（常に表示） -->
            <div class="space-y-4">
              <div class="bg-slate-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-slate-800 mb-4">絞り込み条件</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <!-- 注文No -->
                  <div>
                    <label
                      for="filter_order_no"
                      class="block text-sm font-medium text-slate-700 mb-2"
                    >
                      注文No
                    </label>
                    <input
                      id="filter_order_no"
                      v-model="filters.orderNo"
                      type="text"
                      placeholder="注文Noを入力"
                      class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>

                  <!-- 注文者 -->
                  <div>
                    <label
                      for="filter_order_user"
                      class="block text-sm font-medium text-slate-700 mb-2"
                    >
                      注文者
                    </label>
                    <select
                      id="filter_order_user"
                      v-model="filters.orderUser"
                      class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
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
                      class="block text-sm font-medium text-slate-700 mb-2"
                    >
                      注文先
                    </label>
                    <div>
                      <input
                        id="filter_com_name"
                        v-model="filters.comName"
                        list="com_name_list"
                        type="text"
                        placeholder="注文先を入力または選択"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
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
                      class="block text-sm font-medium text-slate-700 mb-2"
                    >
                      品名・品番
                    </label>
                    <input
                      id="filter_product"
                      v-model="filters.product"
                      list="product_list"
                      type="text"
                      placeholder="品名・品番を入力または選択"
                      class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                    <datalist id="product_list">
                      <option
                        v-for="product in productOptions"
                        :key="product"
                        :value="product"
                      >
                        {{ product }}
                      </option>
                    </datalist>
                  </div>
                </div>
                <div class="mt-4 flex justify-between items-center">
                  <span v-if="isSearching" class="text-sm text-slate-500">発注データを読み込み中...</span>
                  <span v-else class="text-sm text-slate-500">入力・選択すると自動で絞り込まれます</span>
                  <button
                    @click="clearFilters"
                    class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-6 rounded-lg"
                  >
                    条件クリア
                  </button>
                </div>
              </div>

              <!-- 検索結果表示 -->
              <div v-if="searchResults.length > 0" class="mt-4">
                <h4 class="text-md font-medium text-slate-800 mb-2">
                  検索結果 ({{ searchResults.length }}件)
                </h4>
                <div class="border border-slate-200 rounded-lg overflow-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-50">
                      <tr>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文No</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">画像</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文者</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文日</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">希望納期</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">注文先</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">品名</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">品番</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">数量</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">単価</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">金額</th>
                        <th class="px-4 py-3 bg-slate-100 whitespace-nowrap">操作</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="order in searchResults" :key="order.id">
                        <td class="px-4 py-6 whitespace-nowrap">{{ order.order_no || "-" }}</td>
                        <td class="w-24 px-4 py-6 whitespace-nowrap">
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
                          <span v-else class="text-slate-400">-</span>
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ order.order_user || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{
                            order.order_date
                              ? new Date(order.order_date).toLocaleDateString(
                                  "ja-JP"
                                )
                              : "-"
                          }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{
                            order.desire_delivery_date
                              ? new Date(
                                  order.desire_delivery_date
                                ).toLocaleDateString("ja-JP")
                              : "未指定"
                          }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ order.com_name || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ order.name || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">{{ order.s_name || "-" }}</td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{ order.quantity ? order.quantity + (order.order_unit || "") : "-" }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{ order.price ? order.price + "円" : "-" }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          {{ order.price ? order.calc_price + "円" : "-" }}
                        </td>
                        <td class="px-4 py-6 whitespace-nowrap">
                          <button
                            @click="linkOrder(order)"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-1 px-3 rounded-lg text-xs"
                          >
                            追加
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
          <!-- /左右分割ラッパー -->
        </div>
      </div>
    </div>

    <!-- 書類画像 拡大モーダル（中央・大きめ・ズーム/ドラッグ/ホイール対応） -->
    <div
      v-if="showImageModal"
      class="fixed inset-0 z-50 bg-black/80 flex flex-col"
      @mousemove="onDrag"
      @mouseup="endDrag"
    >
      <!-- 操作バー -->
      <div class="flex items-center justify-between px-4 py-3 text-white bg-black/40">
        <span class="text-sm font-medium truncate">
          {{ displayImageTitle }}　拡大率 {{ Math.round(imageScale * 100) }}%
          <span v-if="rotationAngle !== 0">/ 回転 {{ rotationAngle }}°</span>
        </span>
        <div class="flex items-center gap-2">
          <button @click="zoomOut" class="bg-white/10 hover:bg-white/25 px-3 py-1.5 rounded text-sm" title="縮小">－</button>
          <button @click="zoomIn" class="bg-white/10 hover:bg-white/25 px-3 py-1.5 rounded text-sm" title="拡大">＋</button>
          <button @click="resetZoom" class="bg-white/10 hover:bg-white/25 px-3 py-1.5 rounded text-sm" title="等倍に戻す">等倍</button>
          <button @click="previewRotate(90)" class="bg-white/10 hover:bg-white/25 px-3 py-1.5 rounded text-sm" title="90°回転">↻</button>
          <button @click="closeImageModal" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded text-sm font-semibold">✕ 閉じる</button>
        </div>
      </div>
      <!-- ビューア本体 -->
      <div
        class="flex-1 overflow-hidden flex items-center justify-center"
        :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
        style="touch-action: none;"
        @mousedown.prevent="startDrag"
        @wheel.prevent="onWheelZoom"
        @dblclick="resetZoom"
      >
        <img
          :src="displayImageUrl"
          :alt="displayImageTitle"
          class="select-none"
          :style="{
            transform: `translate(${panX}px, ${panY}px) rotate(${rotationAngle}deg) scale(${imageScale})`,
            transformOrigin: 'center center',
            maxHeight: '82vh',
            maxWidth: '92vw',
            transition: isDragging ? 'none' : 'transform 0.15s ease',
          }"
          draggable="false"
        />
      </div>
      <div class="text-center text-white/70 text-xs py-2 bg-black/40">
        ドラッグで移動 ・ ホイールで拡大縮小 ・ ダブルクリックで等倍
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
            class="text-slate-500 hover:text-slate-700"
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
            class="text-slate-500 hover:text-slate-700"
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
            <label class="block text-sm font-medium text-slate-700 mb-3">
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
                <span class="text-sm text-slate-700">分納</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="deliveryTypeSelection"
                  value="complete"
                  class="mr-2"
                />
                <span class="text-sm text-slate-700">完納</span>
              </label>
            </div>
          </div>

          <!-- サイネージディスプレイ選択 -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-3">
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
                <span class="text-sm text-slate-700">表示あり</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="signageDisplaySelection"
                  value="hide"
                  class="mr-2"
                />
                <span class="text-sm text-slate-700">表示なし</span>
              </label>
            </div>
          </div>

          <!-- 在庫加算（格納先・数量） -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-3">
              在庫加算
            </label>

            <!-- 格納先の読み込み中 -->
            <p v-if="loadingStorages" class="text-sm text-slate-500">
              格納先を確認中...
            </p>

            <!-- 格納先が未登録 -->
            <p
              v-else-if="storageOptions.length === 0"
              class="text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded p-3"
            >
              この物品には格納先が登録されていないため、在庫加算は行いません。
            </p>

            <!-- 格納先あり -->
            <div v-else class="space-y-3">
              <div>
                <span class="block text-xs text-slate-500 mb-1">格納先</span>
                <!-- 単一の場合は自動選択（選択済みを表示） -->
                <div
                  v-if="storageOptions.length === 1"
                  class="w-full px-3 py-2 border border-slate-200 bg-slate-50 rounded-lg text-sm text-slate-700"
                >
                  {{ storageOptions[0].label }}
                  <span class="text-slate-400">（現在庫: {{ storageOptions[0].current_quantity }}）</span>
                </div>
                <!-- 複数の場合は選択 -->
                <select
                  v-else
                  v-model="selectedStorageId"
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                >
                  <option value="">格納先を選択してください</option>
                  <option
                    v-for="s in storageOptions"
                    :key="s.storage_address_id"
                    :value="s.storage_address_id"
                  >
                    {{ s.label }}（現在庫: {{ s.current_quantity }}）
                  </option>
                </select>
              </div>

              <!-- 換算（換算値を使用するか） -->
              <div>
                <span class="block text-xs text-slate-500 mb-1">換算</span>
                <div class="flex gap-4">
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="conversionSelection"
                      value="use"
                      @change="applyConversion"
                      class="mr-2"
                    />
                    <span class="text-sm text-slate-700">使用する</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="conversionSelection"
                      value="unuse"
                      @change="applyConversion"
                      class="mr-2"
                    />
                    <span class="text-sm text-slate-700">使用しない</span>
                  </label>
                </div>
                <p
                  v-if="conversionSelection === 'use'"
                  class="text-xs text-slate-400 mt-1"
                >
                  発注数量 {{ currentOrderQuantity ?? 0 }} × 換算値
                  {{ currentQuantityPerOrg ?? 1 }}
                </p>
              </div>

              <div>
                <span class="block text-xs text-slate-500 mb-1">加算数量</span>
                <input
                  v-model.number="linkQuantity"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
              </div>
            </div>
          </div>

          <!-- 確定ボタン -->
          <div class="pt-4 border-t border-slate-200">
            <button
              @click="confirmLinkOrder"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg"
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
import Badge from "@/Components/UI/Badge.vue";

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

// 在庫加算（格納先・数量）
const storageOptions = ref([]); // 選択中発注データの物品に登録された格納先一覧
const loadingStorages = ref(false);
const selectedStorageId = ref(""); // 加算先の格納先ID
const linkQuantity = ref(null); // 加算数量
const conversionSelection = ref("use"); // 換算: 'use'(使用する) / 'unuse'(使用しない)、デフォルトは使用する
const currentOrderQuantity = ref(null); // 選択中発注データの発注数量
const currentQuantityPerOrg = ref(null); // 選択中物品の換算値（発注単位あたりの個数）

// 換算選択に応じて加算数量を再計算する
const applyConversion = () => {
  const qty = Number(currentOrderQuantity.value) || 0;
  if (conversionSelection.value === "use") {
    // 換算値（発注単位あたりの個数）× 発注数量。換算値が未設定なら1として扱う
    const per = Number(currentQuantityPerOrg.value) || 1;
    linkQuantity.value = qty * per;
  } else {
    // 換算を使用しない場合は発注数量をそのまま加算数量とする
    linkQuantity.value = qty;
  }
};

// 画像回転のプレビュー角度（累積）
const rotationAngle = ref(0);
const imageScale = ref(1); // 書類画像の拡大率（＋/－・ホイールで変更・0.25〜5倍）
const panX = ref(0); // ドラッグ移動量X
const panY = ref(0); // ドラッグ移動量Y
const isDragging = ref(false);
let dragOrigin = { x: 0, y: 0 };
const showImageModal = ref(false); // 中央の拡大モーダル表示

// 発注データ紐づけ関連
const allInitialOrders = ref([]);
const isSearching = ref(false);

// 絞り込み条件
const filters = ref({
  orderNo: "",
  orderUser: "",
  comName: "",
  product: "",
});

// 紐づけ済み発注データのIDセット（検索候補から除外）
const linkedOrderIds = computed(() => props.linkedOrders.map((o) => o.id));

// 各絞り込み条件のマッチ判定（個別）
const matchOrderNo = (o) => {
  if (!filters.value.orderNo) return true;
  return (o.order_no?.toString() || "")
    .toLowerCase()
    .includes(filters.value.orderNo.toLowerCase());
};
const matchOrderUser = (o) =>
  !filters.value.orderUser || o.order_user === filters.value.orderUser;
const matchComName = (o) => {
  if (!filters.value.comName) return true;
  return (o.com_name || "").toLowerCase().includes(filters.value.comName.toLowerCase());
};
const matchProduct = (o) => {
  if (!filters.value.product) return true;
  const q = filters.value.product.toLowerCase();
  return `${o.name || ""} ${o.s_name || ""}`.toLowerCase().includes(q);
};

// 紐づけ済みを除いた候補
const baseOrders = computed(() =>
  allInitialOrders.value.filter((o) => !linkedOrderIds.value.includes(o.id))
);

// 絞り込み結果（フィルタ変更で自動的に再計算）
const searchResults = computed(() =>
  baseOrders.value.filter(
    (o) => matchOrderNo(o) && matchOrderUser(o) && matchComName(o) && matchProduct(o)
  )
);

// 絞り込み条件の選択肢（自分以外の条件で絞った結果から生成 = faceted）
const uniqueOrderUsers = computed(() => {
  const set = new Set();
  baseOrders.value
    .filter((o) => matchOrderNo(o) && matchComName(o) && matchProduct(o))
    .forEach((o) => o.order_user && set.add(o.order_user));
  return Array.from(set).sort();
});
const comNames = computed(() => {
  const set = new Set();
  baseOrders.value
    .filter((o) => matchOrderNo(o) && matchOrderUser(o) && matchProduct(o))
    .forEach((o) => o.com_name && set.add(o.com_name));
  return Array.from(set).sort();
});
const productOptions = computed(() => {
  const set = new Set();
  baseOrders.value
    .filter((o) => matchOrderNo(o) && matchOrderUser(o) && matchComName(o))
    .forEach((o) => {
      if (o.name) set.add(o.name);
      if (o.s_name) set.add(o.s_name);
    });
  return Array.from(set).sort();
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
  } catch (error) {
    console.error("発注データ取得エラー:", error);
    alert("❌ 発注データの取得に失敗しました。");
    allInitialOrders.value = [];
  } finally {
    isSearching.value = false;
  }
};

// コンポーネントマウント時に発注データを取得（以降はフロント側で自動絞り込み）
onMounted(() => {
  loadInitialOrders();
});

// 絞り込み条件をクリア
const clearFilters = () => {
  filters.value = {
    orderNo: "",
    orderUser: "",
    comName: "",
    product: "",
  };
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
const linkOrder = (order) => {
  selectedOrderId.value = order.id;
  // デフォルト値をリセット
  deliveryTypeSelection.value = "complete";
  signageDisplaySelection.value = "show";
  // 在庫加算関連のリセット
  storageOptions.value = [];
  selectedStorageId.value = "";
  // 換算関連の初期化（デフォルトは換算を使用する）
  conversionSelection.value = "use";
  currentOrderQuantity.value = order.quantity ?? null;
  currentQuantityPerOrg.value = order.quantity_per_org ?? null;
  applyConversion(); // 加算数量の初期値を換算設定に従って算出
  showDeliveryTypeModal.value = true;
  // 物品の格納先候補を取得
  loadStorageOptions(order.stock_id);
};

// 物品(stock)の格納先候補を取得
const loadStorageOptions = async (stockId) => {
  if (!stockId) {
    storageOptions.value = [];
    return;
  }
  loadingStorages.value = true;
  try {
    const response = await fetch(`/api/stocks/${stockId}/storages`);
    if (!response.ok) {
      throw new Error("格納先の取得に失敗しました");
    }
    const data = await response.json();
    storageOptions.value = data || [];
    // 格納先が1件のみの場合は自動選択
    if (storageOptions.value.length === 1) {
      selectedStorageId.value = storageOptions.value[0].storage_address_id;
    }
  } catch (error) {
    console.error("格納先取得エラー:", error);
    storageOptions.value = [];
  } finally {
    loadingStorages.value = false;
  }
};

// 納品種別を選択して紐づけを実行
const confirmLinkOrder = async () => {
  if (!selectedOrderId.value) {
    return;
  }

  // 在庫加算のバリデーション（格納先が登録されている場合のみ）
  let storageAddressId = null;
  let quantity = null;
  if (storageOptions.value.length > 0) {
    if (!selectedStorageId.value) {
      alert("在庫を加算する格納先を選択してください。");
      return;
    }
    if (!linkQuantity.value || linkQuantity.value < 1) {
      alert("加算数量を1以上で入力してください。");
      return;
    }
    storageAddressId = selectedStorageId.value;
    quantity = linkQuantity.value;
  }

  try {
    router.post(
      route("admin.deliveries.link-order", props.delivery.id),
      {
        order_id: selectedOrderId.value,
        delivery_type: deliveryTypeSelection.value, // 'partial' or 'complete'
        signage_display: signageDisplaySelection.value, // 'show' or 'hide'
        storage_address_id: storageAddressId, // 在庫加算先（未登録物品はnull）
        quantity: quantity, // 加算数量（未登録物品はnull）
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
            product: "",
          };
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

// 画像ズーム（＋/－・等倍）
const zoomIn = () => {
  imageScale.value = Math.min(4, Math.round((imageScale.value + 0.25) * 100) / 100);
};
const zoomOut = () => {
  imageScale.value = Math.max(0.25, Math.round((imageScale.value - 0.25) * 100) / 100);
};
const resetZoom = () => {
  imageScale.value = 1;
  panX.value = 0;
  panY.value = 0;
};

// ドラッグで画像を移動（マウス）
const startDrag = (e) => {
  isDragging.value = true;
  dragOrigin = { x: e.clientX - panX.value, y: e.clientY - panY.value };
};
const onDrag = (e) => {
  if (!isDragging.value) return;
  panX.value = e.clientX - dragOrigin.x;
  panY.value = e.clientY - dragOrigin.y;
};
const endDrag = () => {
  isDragging.value = false;
};

// ホイールで拡大縮小（拡大モーダル内）
const onWheelZoom = (e) => {
  const delta = e.deltaY < 0 ? 0.15 : -0.15;
  imageScale.value = Math.min(5, Math.max(0.25, Math.round((imageScale.value + delta) * 100) / 100));
};

// 中央の拡大モーダル
const openImageModal = () => {
  showImageModal.value = true;
};
const closeImageModal = () => {
  showImageModal.value = false;
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
            only: ["delivery", "documentUrl", "qrCodeUrl", "linkedOrders"],
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
