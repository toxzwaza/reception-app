<template>
  <ReceptionLayout
    title="アポイントありの方"
    subtitle="QRコードを読み取るか、受付番号を入力してください"
  >
    <div class="px-6 py-4 h-[calc(100vh-140px)] flex flex-col">
      <!-- QRコード・受付番号を忘れた方 -->
      <div class="mb-4">
        <div class="bg-gradient-to-r from-orange-50 to-red-50 border-l-4 border-orange-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
              <svg
                class="w-6 h-6 text-orange-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
            </div>
            <div class="flex-1 flex items-center justify-between">
              <p class="text-sm font-medium text-gray-800">
                QRコード・受付番号をお忘れの方は
              </p>
              <button
                @click="goToOtherVisitor"
                class="px-6 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-semibold hover:from-orange-600 hover:to-red-600 transition-all duration-200 shadow-md text-sm"
              >
                訪問者登録へ進む
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 grid grid-cols-2 gap-6">
        <!-- QRコード読み取り -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200 p-4 shadow-lg">
          <div class="flex items-center justify-center mb-3">
            <div class="bg-blue-600 text-white px-4 py-1.5 rounded-full text-sm font-bold shadow-md">
              QRコード読み取り
            </div>
          </div>
          <div class="space-y-3">
            <div
              class="relative bg-black rounded-xl overflow-hidden shadow-xl border-4 border-blue-300"
              style="height: 380px"
            >
              <video
                ref="videoElement"
                autoplay
                playsinline
                class="w-full h-full object-cover"
              ></video>
              <div
                v-if="qrScanned"
                class="absolute inset-0 bg-blue-500 bg-opacity-30 flex items-center justify-center backdrop-blur-sm"
              >
                <div class="bg-white rounded-xl p-6 shadow-2xl">
                  <svg
                    class="w-20 h-20 text-blue-600 mx-auto"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  <p class="text-center mt-3 font-bold text-blue-900 text-lg">
                    読み取り成功
                  </p>
                </div>
              </div>
              <!-- スキャンガイドライン -->
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="border-4 border-blue-400 border-dashed rounded-xl opacity-50" style="width: 70%; height: 70%;"></div>
              </div>
            </div>
            <p class="text-sm text-blue-800 text-center font-medium">
              📱 QRコードをカメラに向けてください
            </p>
          </div>
        </div>

        <!-- 受付番号入力 -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200 p-4 shadow-lg">
          <div class="flex items-center justify-center mb-3">
            <div class="bg-blue-600 text-white px-4 py-1.5 rounded-full text-sm font-bold shadow-md">
              受付番号入力
            </div>
          </div>
          <form @submit.prevent="submitReceptionNumber" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-blue-900 mb-3 text-center"
                >受付番号（4桁）</label
              >
              <div class="flex gap-3 justify-center mb-4">
                <div
                  v-for="i in 4"
                  :key="i"
                  class="w-14 h-16 text-center text-3xl font-bold border-3 rounded-xl bg-white flex items-center justify-center transition-all duration-200 relative shadow-md"
                  :class="{
                    'border-blue-600 bg-blue-50 ring-2 ring-blue-300':
                      receptionNumber[i - 1] !== '',
                    'border-blue-400 bg-blue-25 ring-2 ring-blue-200':
                      currentInputIndex === i - 1 &&
                      receptionNumber[i - 1] === '',
                    'border-gray-300 bg-white':
                      currentInputIndex !== i - 1 &&
                      receptionNumber[i - 1] === '',
                  }"
                >
                  <span class="text-blue-900">{{ receptionNumber[i - 1] || "" }}</span>
                  <!-- カーソル表示 -->
                  <div
                    v-if="
                      currentInputIndex === i - 1 &&
                      receptionNumber[i - 1] === ''
                    "
                    class="absolute inset-0 flex items-center justify-center"
                  >
                    <div class="w-1 h-8 bg-blue-600 animate-pulse rounded-full"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 仮想キーボード -->
            <div>
              <div class="grid grid-cols-3 gap-2 max-w-sm mx-auto mb-3">
                <button
                  v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                  :key="num"
                  type="button"
                  @click="inputDigit(num)"
                  class="py-3 bg-white border-2 border-blue-200 rounded-xl text-2xl font-bold text-blue-900 hover:bg-blue-50 hover:border-blue-400 active:bg-blue-100 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-150 shadow-md"
                >
                  {{ num }}
                </button>
                <button
                  type="button"
                  @click="clearLastDigit"
                  class="py-3 bg-gray-200 border-2 border-gray-300 rounded-xl text-xl font-bold text-gray-700 hover:bg-gray-300 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-150 shadow-md"
                >
                  ⌫
                </button>
                <button
                  type="button"
                  @click="inputDigit(0)"
                  class="py-3 bg-white border-2 border-blue-200 rounded-xl text-2xl font-bold text-blue-900 hover:bg-blue-50 hover:border-blue-400 active:bg-blue-100 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-150 shadow-md"
                >
                  0
                </button>
              </div>
            </div>

            <button
              type="submit"
              :disabled="!isReceptionNumberComplete || processing"
              class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-bold hover:from-blue-700 hover:to-blue-800 disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-200 shadow-lg text-lg"
            >
              <span v-if="processing">⏳ 処理中...</span>
              <span v-else>✓ 受付</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import ReceptionLayout from "@/Layouts/ReceptionLayout.vue";
import jsQR from "jsqr";

const videoElement = ref(null);
const qrScanned = ref(false);
const receptionNumber = ref(["", "", "", ""]);
const currentInputIndex = ref(0);
const processing = ref(false);
let stream = null;
let animationFrameId = null;

const isReceptionNumberComplete = computed(() => {
  return receptionNumber.value.every((digit) => digit !== "");
});

onMounted(() => {
  startQRScanner();
});

onUnmounted(() => {
  stopQRScanner();
});

const startQRScanner = async () => {
  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: "environment" },
    });

    if (videoElement.value) {
      videoElement.value.srcObject = stream;
      scanQRCode();
    }
  } catch (error) {
    console.error("カメラの起動に失敗しました:", error);
  }
};

const stopQRScanner = () => {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  if (stream) {
    stream.getTracks().forEach((track) => track.stop());
  }
};

const scanQRCode = () => {
  const video = videoElement.value;
  if (!video) return;

  const canvas = document.createElement("canvas");
  const context = canvas.getContext("2d");

  const scan = () => {
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvas.width, canvas.height);

      const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
      const code = jsQR(imageData.data, imageData.width, imageData.height);

      if (code) {
        handleQRCodeDetected(code.data);
        return;
      }
    }
    animationFrameId = requestAnimationFrame(scan);
  };

  scan();
};

const handleQRCodeDetected = (qrData) => {
  qrScanned.value = true;
  stopQRScanner();

  // QRコードデータを処理
  router.post(route("appointment.check-in-qr"), {
    qr_data: qrData,
  });
};

// 物理キーボード入力を無効化
const preventKeyboardInput = (event) => {
  event.preventDefault();
  return false;
};

// 仮想キーボードで数字を入力
const inputDigit = (digit) => {
  // 現在の入力インデックスまたは空の入力欄を探す
  let targetIndex = currentInputIndex.value;

  // 現在のインデックスが既に入力済みの場合、次の空欄を探す
  if (receptionNumber.value[targetIndex] !== "") {
    targetIndex = receptionNumber.value.findIndex((value) => value === "");
  }

  // 空の入力欄がある場合のみ入力
  if (targetIndex !== -1 && receptionNumber.value[targetIndex] === "") {
    receptionNumber.value[targetIndex] = digit.toString();
    currentInputIndex.value = Math.min(targetIndex + 1, 3);
  }
};

// 最後に入力された数字を削除
const clearLastDigit = () => {
  // 後ろから空でない入力欄を探す
  for (let i = 3; i >= 0; i--) {
    if (receptionNumber.value[i] !== "") {
      receptionNumber.value[i] = "";
      currentInputIndex.value = i;
      break;
    }
  }
};

const submitReceptionNumber = () => {
  if (!isReceptionNumberComplete.value || processing.value) return;

  processing.value = true;
  const number = receptionNumber.value.join("");

  router.post(
    route("appointment.check-in-number"),
    {
      reception_number: number,
    },
    {
      onFinish: () => {
        processing.value = false;
      },
    }
  );
};

const goToOtherVisitor = () => {
  router.visit(route("other-visitor.create"));
};
</script>

