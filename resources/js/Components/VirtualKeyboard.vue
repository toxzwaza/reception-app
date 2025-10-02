<template>
  <div class="fixed bottom-0 left-0 right-0 bg-gradient-to-t from-gray-900 to-gray-800 shadow-2xl border-t-4 border-indigo-500 z-50">
    <div class="max-w-6xl mx-auto p-4">
      <!-- 言語切り替えボタン（テキストキーボードのみ） -->
      <div v-if="type === 'text'" class="flex justify-end mb-2">
        <button
          @click="toggleLanguage"
          class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all duration-150 active:scale-95 shadow-lg flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
          </svg>
          {{ currentLanguage === 'ja' ? 'ABC' : 'あいう' }}
        </button>
      </div>

      <!-- 数字キーボード -->
      <div v-if="type === 'number'" class="grid grid-cols-3 gap-3">
        <button
          v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
          :key="num"
          @click="handleInput(num.toString())"
          class="h-14 bg-gray-700 hover:bg-indigo-600 text-white text-xl font-bold rounded-xl transition-all duration-150 active:scale-95 shadow-lg hover:shadow-indigo-500/50"
        >
          {{ num }}
        </button>
        <button
          @click="handleBackspace"
          class="h-14 bg-red-600 hover:bg-red-700 text-white text-lg font-semibold rounded-xl transition-all duration-150 active:scale-95 shadow-lg flex items-center justify-center"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
          </svg>
        </button>
        <button
          @click="handleInput('0')"
          class="h-14 bg-gray-700 hover:bg-indigo-600 text-white text-xl font-bold rounded-xl transition-all duration-150 active:scale-95 shadow-lg hover:shadow-indigo-500/50"
        >
          0
        </button>
        <button
          @click="handleEnter"
          class="h-14 bg-green-600 hover:bg-green-700 text-white text-lg font-semibold rounded-xl transition-all duration-150 active:scale-95 shadow-lg flex items-center justify-center"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </button>
      </div>

      <!-- テキストキーボード -->
      <div v-else class="space-y-2">
        <!-- 数字行 -->
        <div class="grid grid-cols-10 gap-2">
          <button
            v-for="num in ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0']"
            :key="num"
            @click="handleInput(num)"
            class="h-12 bg-gray-700 hover:bg-gray-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95"
          >
            {{ num }}
          </button>
        </div>

        <!-- 日本語キーボード -->
        <template v-if="currentLanguage === 'ja'">
          <!-- ひらがな行1 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['あ', 'か', 'さ', 'た', 'な', 'は', 'ま', 'や', 'ら', 'わ']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- ひらがな行2 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['い', 'き', 'し', 'ち', 'に', 'ひ', 'み', 'ゆ', 'り', 'を']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- ひらがな行3 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['う', 'く', 'す', 'つ', 'ぬ', 'ふ', 'む', 'よ', 'る', 'ん']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- ひらがな行4 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['え', 'け', 'せ', 'て', 'ね', 'へ', 'め', 'れ', 'ろ', 'っ']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- ひらがな行5 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['お', 'こ', 'そ', 'と', 'の', 'ほ', 'も', 'ょ', 'ゃ', 'ゅ']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- 特殊文字・会社名行 -->
          <div class="grid grid-cols-12 gap-2">
            <button
              @click="handleDakuten"
              class="h-12 bg-orange-600 hover:bg-orange-700 text-white text-xl font-bold rounded-lg transition-all duration-150 active:scale-95 shadow-lg"
              title="濁点"
            >
              ゛
            </button>
            <button
              @click="handleHandakuten"
              class="h-12 bg-orange-600 hover:bg-orange-700 text-white text-xl font-bold rounded-lg transition-all duration-150 active:scale-95 shadow-lg"
              title="半濁点"
            >
              ゜
            </button>
            <button
              @click="handleInput('・')"
              class="h-12 bg-gray-700 hover:bg-gray-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95"
            >
              ・
            </button>
            <button
              @click="handleInput('ー')"
              class="h-12 bg-gray-700 hover:bg-gray-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95"
            >
              ー
            </button>
            <button
              @click="handleInput('株式会社')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              株式会社
            </button>
            <button
              @click="handleInput('有限会社')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              有限会社
            </button>
            <button
              @click="handleInput('合同会社')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              合同会社
            </button>
            <button
              @click="handleInput('（株）')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              （株）
            </button>
          </div>
        </template>

        <!-- アルファベットキーボード -->
        <template v-else>
          <!-- アルファベット行1 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- アルファベット行2 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', '@']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- アルファベット行3 -->
          <div class="grid grid-cols-10 gap-2">
            <button
              v-for="char in ['Z', 'X', 'C', 'V', 'B', 'N', 'M', '.', '-', '_']"
              :key="char"
              @click="handleInput(char)"
              class="h-12 bg-gray-700 hover:bg-indigo-600 text-white text-lg font-semibold rounded-lg transition-all duration-150 active:scale-95 hover:shadow-indigo-500/50"
            >
              {{ char }}
            </button>
          </div>

          <!-- 会社名ショートカット -->
          <div class="grid grid-cols-12 gap-2">
            <button
              @click="handleInput('株式会社')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              株式会社
            </button>
            <button
              @click="handleInput('有限会社')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              有限会社
            </button>
            <button
              @click="handleInput('合同会社')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              合同会社
            </button>
            <button
              @click="handleInput('Inc.')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              Inc.
            </button>
            <button
              @click="handleInput('Ltd.')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              Ltd.
            </button>
            <button
              @click="handleInput('（株）')"
              class="h-12 bg-blue-700 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
            >
              （株）
            </button>
          </div>
        </template>

        <!-- 機能キー行 -->
        <div class="grid grid-cols-4 gap-2">
          <button
            @click="handleBackspace"
            class="h-12 bg-red-600 hover:bg-red-700 text-white text-base font-semibold rounded-lg transition-all duration-150 active:scale-95 shadow-lg flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
            </svg>
            削除
          </button>
          <button
            @click="handleSpace"
            class="h-12 bg-gray-600 hover:bg-gray-500 text-white text-base font-semibold rounded-lg transition-all duration-150 active:scale-95 col-span-2"
          >
            スペース
          </button>
          <button
            @click="handleEnter"
            class="h-12 bg-green-600 hover:bg-green-700 text-white text-base font-semibold rounded-lg transition-all duration-150 active:scale-95 shadow-lg flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            確定
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'text', // 'text' or 'number'
  },
});

const emit = defineEmits(['input', 'backspace', 'enter', 'space', 'dakuten', 'handakuten']);

const currentLanguage = ref('ja'); // 'ja' or 'en'

const toggleLanguage = () => {
  currentLanguage.value = currentLanguage.value === 'ja' ? 'en' : 'ja';
};

const handleInput = (value) => {
  emit('input', value);
};

const handleBackspace = () => {
  emit('backspace');
};

const handleEnter = () => {
  emit('enter');
};

const handleSpace = () => {
  emit('space');
};

// 濁点変換
const handleDakuten = () => {
  emit('dakuten');
};

// 半濁点変換
const handleHandakuten = () => {
  emit('handakuten');
};
</script>

<style scoped>
button {
  -webkit-tap-highlight-color: transparent;
  user-select: none;
  -webkit-user-select: none;
}
</style>

